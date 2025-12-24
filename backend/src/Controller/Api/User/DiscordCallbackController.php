<?php

namespace App\Controller\Api\User;

use App\DTO\Response\User\UserResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordCallbackController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly ParameterBagInterface $params
    ) {
    }

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

        $clientId = $this->params->get('env(DISCORD_CLIENT_ID)');
        $clientSecret = $this->params->get('env(DISCORD_CLIENT_SECRET)');
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
                $user->setDiscordAvatar(
                    $discordUser['avatar'] ?
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

            // If user is not logged in and state action is 'login', handle login/registration
            if (!$user && isset($stateData['action']) && $stateData['action'] === 'login') {
                // Check if Discord account exists
                if ($existingUser) {
                    // User exists, generate JWT token and login
                    $token = $this->jwtManager->create($existingUser);
                    $userResponse = UserResponse::fromEntity($existingUser);

                    return new Response(
                        '<html><body><script>window.opener.postMessage({type:"discord_login_success",token:"' . $token . '",user:' . json_encode($userResponse) . '}, "*");window.close();</script><p>Logged in successfully! Redirecting...</p></body></html>'
                    );
                }

                // Discord account doesn't exist, create new user and register
                $newUser = new User();
                $newUser->setEmail($discordUser['email'] ?? $discordUser['id'] . '@discord.local');
                $newUser->setUsername($discordUser['username'] . '#' . $discordUser['discriminator']);
                $newUser->setDiscordId($discordUser['id']);
                $newUser->setDiscordUsername($discordUser['username'] . '#' . $discordUser['discriminator']);
                $newUser->setDiscordAvatar(
                    $discordUser['avatar'] ?
                    sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $discordUser['id'], $discordUser['avatar']) :
                    null
                );
                $newUser->setOauthProvider('discord');
                $newUser->setOauthId($discordUser['id']);
                $newUser->setAvatar(
                    $discordUser['avatar'] ?
                    sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $discordUser['id'], $discordUser['avatar']) :
                    null
                );

                $this->entityManager->persist($newUser);
                $this->entityManager->flush();

                // Generate JWT token for new user
                $token = $this->jwtManager->create($newUser);
                $userResponse = UserResponse::fromEntity($newUser);

                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"discord_login_success",token:"' . $token . '",user:' . json_encode($userResponse) . '}, "*");window.close();</script><p>Account created! Redirecting...</p></body></html>'
                );
            }

            // Invalid state
            return new Response(
                '<html><body><script>window.opener.postMessage({type:"discord_error",message:"Invalid OAuth state"}, "*");window.close();</script></body></html>'
            );

        } catch (\Exception $e) {
            return new Response(
                '<html><body><script>window.opener.postMessage({type:"discord_error",message:"Failed to connect Discord: ' . addslashes($e->getMessage()) . '"}, "*");window.close();</script></body></html>'
            );
        }
    }
}
