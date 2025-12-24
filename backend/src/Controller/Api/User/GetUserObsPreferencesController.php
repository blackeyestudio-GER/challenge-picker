<?php

namespace App\Controller\Api\User;

use App\Repository\UserRepository;
use App\Service\ObsPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetUserObsPreferencesController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ObsPreferenceService $obsPreferenceService,
    ) {
    }

    #[Route('/api/user/{uuid}/obs-preferences', name: 'get_user_obs_preferences', methods: ['GET'])]
    public function __invoke(string $uuid): JsonResponse
    {
        // Find user by UUID
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'User not found'],
            ], 404);
        }

        // Get or create OBS preferences for this user
        $preferences = $this->obsPreferenceService->getOrCreatePreferences($user);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'showTimerInSetup' => $preferences->isShowTimerInSetup(),
                'showTimerInActive' => $preferences->isShowTimerInActive(),
                'showTimerInPaused' => $preferences->isShowTimerInPaused(),
                'showTimerInCompleted' => $preferences->isShowTimerInCompleted(),
                'showStatusInSetup' => $preferences->isShowStatusInSetup(),
                'showStatusInActive' => $preferences->isShowStatusInActive(),
                'showStatusInPaused' => $preferences->isShowStatusInPaused(),
                'showStatusInCompleted' => $preferences->isShowStatusInCompleted(),
                'timerPosition' => $preferences->getTimerPosition(),
                'timerDesign' => $preferences->getTimerDesign(),
                'statusDesign' => $preferences->getStatusDesign(),
                'rulesDesign' => $preferences->getRulesDesign(),
                'chromaKeyColor' => $preferences->getChromaKeyColor(),
            ],
        ], 200);
    }
}
