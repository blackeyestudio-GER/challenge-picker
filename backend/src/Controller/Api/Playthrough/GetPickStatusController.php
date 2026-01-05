<?php

namespace App\Controller\Api\Playthrough;

use App\Entity\Playthrough;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/pick-status', name: 'api_playthrough_get_pick_status', methods: ['GET'])]
class GetPickStatusController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Find active playthrough (setup, active, or paused)
        $playthrough = $this->playthroughRepository->findActiveByUser($user);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_ACTIVE_PLAYTHROUGH',
                    'message' => 'No active playthrough found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Only allow pick status for active sessions
        if ($playthrough->getStatus() !== Playthrough::STATUS_ACTIVE) {
            return $this->json([
                'success' => true,
                'data' => [
                    'canPick' => false,
                    'rateLimitSeconds' => null,
                    'cooldownRuleIds' => [],
                    'availableRulesCount' => 0,
                    'message' => 'Session not active',
                ],
            ]);
        }

        // Calculate rate limit status
        $canPick = true;
        $rateLimitSeconds = null;
        $message = 'Ready to draw';

        $lastPickAt = $playthrough->getLastPickAt();
        if ($lastPickAt !== null) {
            $now = new \DateTimeImmutable();
            $secondsSinceLastPick = $now->getTimestamp() - $lastPickAt->getTimestamp();
            if ($secondsSinceLastPick < 2) {
                $canPick = false;
                $rateLimitSeconds = 2 - $secondsSinceLastPick;
                $message = sprintf('Wait %ds', $rateLimitSeconds);
            }
        }

        // Get cooldown info
        $cooldownRuleIds = $playthrough->getCooldownRuleIds() ?? [];

        // Get total available non-default rules count
        $configuration = $playthrough->getConfiguration();
        $availableRulesCount = 0;
        if (isset($configuration['rules']) && is_array($configuration['rules'])) {
            foreach ($configuration['rules'] as $ruleConfig) {
                if (
                    is_array($ruleConfig)
                    && isset($ruleConfig['ruleId'])
                    && (!isset($ruleConfig['isDefault']) || $ruleConfig['isDefault'] !== true)
                    && (!isset($ruleConfig['isEnabled']) || $ruleConfig['isEnabled'] === true)
                ) {
                    ++$availableRulesCount;
                }
            }
        }

        return $this->json([
            'success' => true,
            'data' => [
                'canPick' => $canPick,
                'rateLimitSeconds' => $rateLimitSeconds,
                'cooldownRuleIds' => $cooldownRuleIds,
                'availableRulesCount' => $availableRulesCount,
                'message' => $message,
            ],
        ]);
    }
}
