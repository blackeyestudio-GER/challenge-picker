<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ConnectTwitchController extends AbstractController
{
    #[Route('/api/user/connect/twitch', name: 'api_user_connect_twitch', methods: ['GET'])]
    public function __invoke(#[CurrentUser] ?User $user = null): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'User must be logged in to connect Twitch',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Generate Twitch OAuth URL
        $clientId = $_ENV['TWITCH_CLIENT_ID'] ?? 'your_twitch_client_id';
        $redirectUri = $_ENV['TWITCH_REDIRECT_URI'] ?? 'http://localhost:8090/api/user/connect/twitch/callback';

        // Encode user UUID in state parameter so callback knows which user to connect
        $stateData = [
            'user_uuid' => $user->getUuid(),
            'random' => bin2hex(random_bytes(8)),
        ];
        $state = base64_encode(json_encode($stateData));

        $twitchAuthUrl = sprintf(
            'https://id.twitch.tv/oauth2/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=user:read:email&state=%s',
            $clientId,
            urlencode($redirectUri),
            urlencode($state)
        );

        return $this->json([
            'success' => true,
            'data' => [
                'authUrl' => $twitchAuthUrl,
                'state' => $state,
            ],
        ], Response::HTTP_OK);
    }
}
