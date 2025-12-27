<?php

namespace App\Controller\Api\Playthrough;

use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/counters/decrement', name: 'api_playthrough_counter_decrement', methods: ['POST'])]
class DecrementCounterController extends AbstractController
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
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Authentication required',
                    ],
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Find user's active playthrough
            $playthrough = $this->playthroughRepository->findOneBy([
                'user' => $user,
                'status' => 'active',
            ]);

            if (!$playthrough) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NO_ACTIVE_PLAYTHROUGH',
                        'message' => 'No active playthrough found. Start a playthrough first.',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Get index parameter (which counter to decrement: 1st, 2nd, etc.)
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
            $newAmount = max(0, $previousAmount - $amount);

            $playthroughRule->setCurrentAmount($newAmount);

            // Mark as completed if reaches 0
            if ($newAmount === 0) {
                $playthroughRule->setIsActive(false);
                $playthroughRule->setCompletedAt(new \DateTimeImmutable());
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'ruleId' => $playthroughRule->getRule()->getId(),
                    'ruleName' => $playthroughRule->getRule()->getName(),
                    'previousAmount' => $previousAmount,
                    'currentAmount' => $newAmount,
                    'completed' => $newAmount === 0,
                ],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to decrement counter: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DECREMENT_FAILED',
                    'message' => 'Failed to decrement counter',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
