<?php

namespace App\Controller\Api\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginDiscordController extends AbstractController
{
    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
    }

    #[Route('/api/auth/discord/login', name: 'api_auth_discord_login', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        // Generate Discord OAuth URL for login (no user UUID in state)
        $clientId = $this->params->get('env(DISCORD_CLIENT_ID)');
        $redirectUri = $this->params->get('env(DISCORD_REDIRECT_URI)');

        // State for login doesn't include user UUID
        $stateData = [
            'action' => 'login',
            'random' => bin2hex(random_bytes(8)),
        ];
        $state = base64_encode(json_encode($stateData));

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
