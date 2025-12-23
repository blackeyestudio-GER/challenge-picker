<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TwitchCallbackController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/api/user/connect/twitch/callback', name: 'api_user_connect_twitch_callback', methods: ['GET'])]
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

        $clientId = $_ENV['TWITCH_CLIENT_ID'] ?? '';
        $clientSecret = $_ENV['TWITCH_CLIENT_SECRET'] ?? '';
        $redirectUri = $_ENV['TWITCH_REDIRECT_URI'] ?? 'http://localhost:8090/api/user/connect/twitch/callback';

        try {
            // Exchange code for access token
            $tokenResponse = $this->httpClient->request('POST', 'https://id.twitch.tv/oauth2/token', [
                'body' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ],
            ]);

            $tokenData = $tokenResponse->toArray();
            $accessToken = $tokenData['access_token'];

            // Get Twitch user info
            $userResponse = $this->httpClient->request('GET', 'https://api.twitch.tv/helix/users', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Client-Id' => $clientId,
                ],
            ]);

            $twitchData = $userResponse->toArray();
            $twitchUser = $twitchData['data'][0] ?? null;

            if (!$twitchUser) {
                throw new \Exception('Failed to get Twitch user data');
            }
            
            // Check if Twitch account is already connected to another user
            $existingUser = $this->userRepository->findOneBy(['twitchId' => $twitchUser['id']]);
            
            if ($existingUser && $user && $existingUser->getUuid() !== $user->getUuid()) {
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"twitch_error",message:"This Twitch account is already connected to another user"}, "*");window.close();</script></body></html>'
                );
            }

            // If user is logged in, connect Twitch to their account
            if ($user) {
                $user->setTwitchId($twitchUser['id']);
                $user->setTwitchUsername($twitchUser['login']);
                $user->setTwitchAvatar($twitchUser['profile_image_url'] ?? null);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // Return success HTML that closes window and notifies parent
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"twitch_connected",username:"' . $user->getTwitchUsername() . '"}, "*");window.close();</script><p>Twitch connected successfully! You can close this window.</p></body></html>'
                );
            }

            // If user is not logged in, this is a login attempt
            // Check if Twitch account exists
            if ($existingUser) {
                // Generate JWT token for this user
                // You'll need to implement this using your JWT service
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"twitch_login_success"}, "*");window.close();</script><p>Logged in successfully! Redirecting...</p></body></html>'
                );
            }

            // Twitch account doesn't exist, need to register
            return new Response(
                '<html><body><script>window.opener.postMessage({type:"twitch_register_needed",twitchData:' . json_encode($twitchUser) . '}, "*");window.close();</script></body></html>'
            );

        } catch (\Exception $e) {
            return new Response(
                '<html><body><script>window.opener.postMessage({type:"twitch_error",message:"Failed to connect Twitch: ' . addslashes($e->getMessage()) . '"}, "*");window.close();</script></body></html>'
            );
        }
    }
}

