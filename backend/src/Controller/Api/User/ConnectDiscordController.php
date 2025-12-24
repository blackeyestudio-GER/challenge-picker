<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ConnectDiscordController extends AbstractController
{
    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
    }

    #[Route('/api/user/connect/discord', name: 'api_user_connect_discord', methods: ['GET'])]
    public function __invoke(#[CurrentUser] ?User $user = null): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'User must be logged in to connect Discord',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Generate Discord OAuth URL
        $clientId = $this->params->get('env(DISCORD_CLIENT_ID)');
        $redirectUri = $this->params->get('env(DISCORD_REDIRECT_URI)');

        // Encode user UUID in state parameter so callback knows which user to connect
        $stateData = [
            'user_uuid' => $user->getUuid(),
            'random' => bin2hex(random_bytes(8)),
        ];
        $state = base64_encode(json_encode($stateData));

        $discordAuthUrl = sprintf(
            'https://discord.com/api/oauth2/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=identify&state=%s',
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
