<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordCallbackController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/api/user/connect/discord/callback', name: 'api_user_connect_discord_callback', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $code = $request->query->get('code');
        $state = $request->query->get('state');
        
        // Decode state to get user UUID (if connecting to existing account)
        $stateData = json_decode(base64_decode($state), true);
        $userUuid = $stateData['user_uuid'] ?? null;
        
        $user = null;
        if ($userUuid) {
            $user = $this->userRepository->findOneBy(['uuid' => $userUuid]);
        }
        
        if (!$code) {
            return new Response('<html><body><script>window.close();</script><p>Authorization cancelled. You can close this window.</p></body></html>');
        }

        $clientId = $_ENV['DISCORD_CLIENT_ID'] ?? '';
        $clientSecret = $_ENV['DISCORD_CLIENT_SECRET'] ?? '';
        $redirectUri = $_ENV['DISCORD_REDIRECT_URI'] ?? 'http://localhost:8090/api/user/connect/discord/callback';

        try {
            // Exchange code for access token
            $tokenResponse = $this->httpClient->request('POST', 'https://discord.com/api/oauth2/token', [
                'body' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $tokenData = $tokenResponse->toArray();
            $accessToken = $tokenData['access_token'];

            // Get Discord user info
            $userResponse = $this->httpClient->request('GET', 'https://discord.com/api/users/@me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $discordUser = $userResponse->toArray();
            
            // Check if Discord account is already connected to another user
            $existingUser = $this->userRepository->findOneBy(['discordId' => $discordUser['id']]);
            
            if ($existingUser && $user && $existingUser->getUuid() !== $user->getUuid()) {
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"discord_error",message:"This Discord account is already connected to another user"}, "*");window.close();</script></body></html>'
                );
            }

            // If user is logged in, connect Discord to their account
            if ($user) {
                $user->setDiscordId($discordUser['id']);
                $user->setDiscordUsername($discordUser['username'] . '#' . $discordUser['discriminator']);
                $user->setDiscordAvatar($discordUser['avatar'] ? 
                    sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $discordUser['id'], $discordUser['avatar']) : 
                    null
                );

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // Return success HTML that closes window and notifies parent
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"discord_connected",username:"' . $user->getDiscordUsername() . '"}, "*");window.close();</script><p>Discord connected successfully! You can close this window.</p></body></html>'
                );
            }

            // If user is not logged in, this is a login attempt
            // Check if Discord account exists
            if ($existingUser) {
                // Generate JWT token for this user
                // You'll need to implement this using your JWT service
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"discord_login_success"}, "*");window.close();</script><p>Logged in successfully! Redirecting...</p></body></html>'
                );
            }

            // Discord account doesn't exist, need to register
            return new Response(
                '<html><body><script>window.opener.postMessage({type:"discord_register_needed",discordData:' . json_encode($discordUser) . '}, "*");window.close();</script></body></html>'
            );

        } catch (\Exception $e) {
            return new Response(
                '<html><body><script>window.opener.postMessage({type:"discord_error",message:"Failed to connect Discord: ' . addslashes($e->getMessage()) . '"}, "*");window.close();</script></body></html>'
            );
        }
    }
}

