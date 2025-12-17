<?php

namespace App\Controller\Api\Playthrough;

use App\Repository\PlaythroughRepository;
use App\Service\PlaythroughService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TogglePlaythroughRuleController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly PlaythroughService $playthroughService
    ) {
    }

    #[Route('/api/playthroughs/{uuid}/rules/{ruleId}/toggle', name: 'api_playthrough_rule_toggle', methods: ['PUT'])]
    public function __invoke(string $uuid, int $ruleId): JsonResponse
    {
        // Get authenticated user
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $uuid]);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough not found'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify the playthrough belongs to the authenticated user
        if ($playthrough->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You do not have access to this playthrough'
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $playthroughRule = $this->playthroughService->toggleRule($playthrough, $ruleId);

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $playthroughRule->getId(),
                    'ruleId' => $playthroughRule->getRule()->getId(),
                    'isActive' => $playthroughRule->isActive()
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'TOGGLE_ERROR',
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

