<?php

namespace App\Controller\Api\Play;

use App\Repository\PlaythroughRepository;
use App\Service\ObsPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetPlaythroughPreferencesController extends AbstractController
{
    public function __construct(
        private PlaythroughRepository $playthroughRepository,
        private ObsPreferenceService $obsPreferenceService
    ) {
    }

    #[Route('/api/play/{uuid}/preferences', name: 'get_playthrough_preferences', methods: ['GET'])]
    public function __invoke(string $uuid): JsonResponse
    {
        // Find playthrough by UUID (public, no auth required)
        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $uuid]);

        if (!$playthrough) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'Playthrough not found'],
            ], 404);
        }

        // Get the gamehost's OBS preferences
        $user = $playthrough->getUser();
        $preferences = $this->obsPreferenceService->getOrCreatePreferences($user);

        // Return all OBS preferences (public for OBS overlays)
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
