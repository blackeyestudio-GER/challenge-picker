<?php

namespace App\Controller\Api\Obs;

use App\DTO\Response\Obs\ObsPreferenceResponse;
use App\Entity\User;
use App\Service\ObsPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetObsPreferencesController extends AbstractController
{
    public function __construct(
        private ObsPreferenceService $obsPreferenceService
    ) {
    }

    #[Route('/api/users/me/obs-preferences', name: 'get_obs_preferences', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'Unauthorized'],
            ], 401);
        }

        $preferences = $this->obsPreferenceService->getOrCreatePreferences($user);

        return new JsonResponse(
            ObsPreferenceResponse::fromEntity($preferences),
            200
        );
    }
}
