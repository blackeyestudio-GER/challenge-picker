<?php

namespace App\Controller\Api\Obs;

use App\DTO\Response\Obs\ObsPreferenceResponse;
use App\Service\AuthService;
use App\Service\ObsPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetObsPreferencesController extends AbstractController
{
    public function __construct(
        private AuthService $authService,
        private ObsPreferenceService $obsPreferenceService
    ) {
    }

    #[Route('/api/users/me/obs-preferences', name: 'get_obs_preferences', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        // Get authenticated user
        $user = $this->authService->getUserFromToken($request);
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'Unauthorized']
            ], 401);
        }

        $preferences = $this->obsPreferenceService->getOrCreatePreferences($user);

        return new JsonResponse(
            ObsPreferenceResponse::fromEntity($preferences),
            200
        );
    }
}

