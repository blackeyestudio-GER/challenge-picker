<?php

namespace App\Controller\Api\Auth;

use App\DTO\Response\Auth\OAuthProvidersResponse;
use App\Service\TwitchOAuthSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/oauth/providers', name: 'api_oauth_providers', methods: ['GET'])]
class GetOAuthProvidersController extends AbstractController
{
    public function __construct(
        private readonly TwitchOAuthSettings $twitchOAuthSettings
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return $this->json(
            OAuthProvidersResponse::fromValues($this->twitchOAuthSettings->isLinkingConfigured()),
            Response::HTTP_OK
        );
    }
}
