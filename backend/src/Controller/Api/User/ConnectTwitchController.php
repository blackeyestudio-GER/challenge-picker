<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Service\TwitchOAuthSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ConnectTwitchController extends AbstractController
{
    public function __construct(
        private readonly TwitchOAuthSettings $twitchOAuthSettings
    ) {
    }

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

        if (!$this->twitchOAuthSettings->isLinkingConfigured()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'TWITCH_OAUTH_NOT_CONFIGURED',
                    'message' => 'Twitch linking is not configured. Add TWITCH_CLIENT_ID and TWITCH_CLIENT_SECRET from dev.twitch.tv when ready.',
                ],
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $clientIdRaw = $_ENV['TWITCH_CLIENT_ID'] ?? '';
        $redirectUriRaw = $_ENV['TWITCH_REDIRECT_URI'] ?? 'http://localhost:8090/api/user/connect/twitch/callback';
        $clientId = is_string($clientIdRaw) ? $clientIdRaw : '';
        $redirectUri = is_string($redirectUriRaw) ? $redirectUriRaw : 'http://localhost:8090/api/user/connect/twitch/callback';

        // Encode user UUID in state parameter so callback knows which user to connect
        $stateData = [
            'user_uuid' => $user->getUuid()->toRfc4122(),
            'random' => bin2hex(random_bytes(8)),
        ];
        $stateJson = json_encode($stateData, JSON_THROW_ON_ERROR);
        $state = base64_encode($stateJson);

        $twitchAuthUrl = sprintf(
            'https://id.twitch.tv/oauth2/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=user:read:email&state=%s',
            rawurlencode($clientId),
            rawurlencode($redirectUri),
            rawurlencode($state)
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
