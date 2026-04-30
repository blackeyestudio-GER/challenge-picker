<?php

namespace App\Controller\Api\Playthrough;

use App\Entity\User;
use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/counters/increment', name: 'api_playthrough_counter_increment', methods: ['POST'])]
class IncrementCounterController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof User) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Authentication required',
                    ],
                ], Response::HTTP_UNAUTHORIZED);
            }

            $playthrough = $this->playthroughRepository->findActiveByUser($user);

            if (!$playthrough) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NO_ACTIVE_PLAYTHROUGH',
                        'message' => 'No active playthrough found. Start a playthrough first.',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Get index parameter (which counter to increment: 1st, 2nd, etc.)
            $index = (int) $request->query->get('index', 1);
            $amount = (int) $request->query->get('amount', 1);

            if ($index < 1) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_INDEX',
                        'message' => 'Index must be 1 or greater',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get all active counter-based rules (where currentAmount is not null)
            $counterRules = [];
            foreach ($playthrough->getPlaythroughRules() as $playthroughRule) {
                if ($playthroughRule->isActive() && $playthroughRule->getCurrentAmount() !== null) {
                    $counterRules[] = $playthroughRule;
                }
            }

            if (empty($counterRules)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NO_COUNTER_RULES',
                        'message' => 'No active counter-based rules found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Get the Nth counter rule (index-1 because index is 1-based)
            $targetIndex = $index - 1;
            if (!isset($counterRules[$targetIndex])) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INDEX_OUT_OF_RANGE',
                        'message' => "Counter index {$index} not found. Only " . count($counterRules) . ' counter rule(s) active.',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $playthroughRule = $counterRules[$targetIndex];
            $previousAmount = $playthroughRule->getCurrentAmount();
            $newAmount = $previousAmount + $amount;

            $playthroughRule->setCurrentAmount($newAmount);

            // Reactivate if was completed
            if (!$playthroughRule->isActive()) {
                $playthroughRule->setIsActive(true);
                $playthroughRule->setCompletedAt(null);
            }

            $this->entityManager->flush();

            $rule = $playthroughRule->getRule();
            if (!$rule) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULE_NOT_FOUND',
                        'message' => 'Rule not found',
                    ],
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'ruleId' => $rule->getId(),
                    'ruleName' => $rule->getName(),
                    'previousAmount' => $previousAmount,
                    'currentAmount' => $newAmount,
                    'completed' => false,
                ],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to increment counter: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INCREMENT_FAILED',
                    'message' => 'Failed to increment counter',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
