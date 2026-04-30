<?php

namespace App\Controller\Api\User;

use App\Repository\UserRepository;
use App\Service\ArrayTypeHelper;
use App\Service\TwitchOAuthSettings;
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
        private readonly UserRepository $userRepository,
        private readonly TwitchOAuthSettings $twitchOAuthSettings
    ) {
    }

    #[Route('/api/user/connect/twitch/callback', name: 'api_user_connect_twitch_callback', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->twitchOAuthSettings->isLinkingConfigured()) {
            return new Response(
                '<html><body><script>window.opener.postMessage({type:"twitch_error",message:"Twitch OAuth is not configured on this server."}, "*");window.close();</script><p>Twitch is not configured.</p></body></html>'
            );
        }

        $code = $request->query->get('code');
        $stateParam = $request->query->get('state');
        $state = is_string($stateParam) ? $stateParam : '';

        // Decode state to get user UUID (if connecting to existing account)
        $decodedState = base64_decode($state, true);
        if (false === $decodedState) {
            $stateData = [];
        } else {
            $decoded = json_decode($decodedState, true);
            $stateData = is_array($decoded) ? $decoded : [];
        }
        /** @var array<string, mixed> $stateData */
        $userUuid = ArrayTypeHelper::tryGetString($stateData, 'user_uuid');

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

            /** @var array<string, mixed> $tokenData */
            $tokenData = $tokenResponse->toArray();
            $accessToken = ArrayTypeHelper::getString($tokenData, 'access_token');

            // Get Twitch user info
            $userResponse = $this->httpClient->request('GET', 'https://api.twitch.tv/helix/users', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Client-Id' => $clientId,
                ],
            ]);

            /** @var array<string, mixed> $twitchData */
            $twitchData = $userResponse->toArray();
            $twitchDataArray = ArrayTypeHelper::tryGetArray($twitchData, 'data');
            $firstTwitch = null;
            if (is_array($twitchDataArray) && [] !== $twitchDataArray) {
                $row = $twitchDataArray[array_key_first($twitchDataArray)];
                $firstTwitch = is_array($row) ? $row : null;
            }
            $twitchUser = $firstTwitch;

            if (!is_array($twitchUser)) {
                throw new \Exception('Failed to get Twitch user data');
            }
            /** @var array<string, mixed> $twitchUser */

            // Check if Twitch account is already connected to another user
            $twitchId = ArrayTypeHelper::getString($twitchUser, 'id');
            $existingUser = $this->userRepository->findOneBy(['twitchId' => $twitchId]);

            if ($existingUser && $user && $existingUser->getUuid() !== $user->getUuid()) {
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"twitch_error",message:"This Twitch account is already connected to another user"}, "*");window.close();</script></body></html>'
                );
            }

            // If user is logged in, connect Twitch to their account
            if ($user) {
                $user->setTwitchId($twitchId);
                $user->setTwitchUsername(ArrayTypeHelper::getString($twitchUser, 'login'));
                $user->setTwitchAvatar(ArrayTypeHelper::tryGetString($twitchUser, 'profile_image_url'));

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
