<?php

namespace App\Controller\Api\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginDiscordController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/api/auth/discord/login', name: 'api_auth_discord_login', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        // Generate Discord OAuth URL for login (no user UUID in state)
        $clientId = $_ENV['DISCORD_CLIENT_ID'] ?? throw new \RuntimeException('DISCORD_CLIENT_ID not configured');
        $redirectUri = $_ENV['DISCORD_REDIRECT_URI'] ?? 'http://localhost:8090/api/user/connect/discord/callback';
        if (!is_string($clientId) || !is_string($redirectUri)) {
            throw new \RuntimeException('Discord OAuth environment is invalid');
        }

        // State for login doesn't include user UUID
        $stateData = [
            'action' => 'login',
            'random' => bin2hex(random_bytes(8)),
        ];
        $stateJson = json_encode($stateData);
        if ($stateJson === false) {
            throw new \RuntimeException('Failed to encode OAuth state');
        }

        $state = base64_encode($stateJson);

        $discordAuthUrl = sprintf(
            'https://discord.com/api/oauth2/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=identify%%20email&state=%s',
            $clientId,
            urlencode($redirectUri),
            urlencode($state)
        );

        return $this->json([
            'success' => true,
            'data' => [
                'authUrl' => $discordAuthUrl,
                'state' => $state,
            ],
        ], Response::HTTP_OK);
    }
}
