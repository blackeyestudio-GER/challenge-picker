<?php

namespace App\Controller\Api\PlaythroughRule;

use App\Entity\User;
use App\Repository\PlaythroughRepository;
use App\Repository\PlaythroughRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IncrementCounterController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRuleRepository $playthroughRuleRepository,
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/playthrough/rules/{id}/increment', name: 'api_playthrough_rule_increment', methods: ['POST'])]
    public function __invoke(int $id): JsonResponse
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
                        'message' => 'No active playthrough found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $playthroughRule = $this->playthroughRuleRepository->find($id);
            if (!$playthroughRule) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULE_NOT_FOUND',
                        'message' => 'Playthrough rule not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            if ($playthroughRule->getPlaythrough()?->getId() !== $playthrough->getId()) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'You do not own this playthrough',
                    ],
                ], Response::HTTP_FORBIDDEN);
            }

            $currentAmount = $playthroughRule->getCurrentAmount();
            if ($currentAmount === null) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_AMOUNT',
                        'message' => 'Not a counter rule',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

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

            $difficultyLevel = $rule->getDifficultyLevels()->first();
            $maxAmount = $difficultyLevel ? $difficultyLevel->getAmount() : null;

            if (!$playthroughRule->isActive() && 0 === $currentAmount) {
                $playthroughRule->setCurrentAmount(1);
                $playthroughRule->setIsActive(true);
                $playthroughRule->setCompletedAt(null);
            } else {
                $newAmount = $currentAmount + 1;
                if (null !== $maxAmount && $newAmount > $maxAmount) {
                    return $this->json([
                        'success' => false,
                        'error' => [
                            'code' => 'COUNTER_AT_MAX',
                            'message' => 'Counter is already at maximum',
                        ],
                    ], Response::HTTP_BAD_REQUEST);
                }
                $playthroughRule->setCurrentAmount($newAmount);
                if (!$playthroughRule->isActive()) {
                    $playthroughRule->setIsActive(true);
                    $playthroughRule->setCompletedAt(null);
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $playthroughRule->getId(),
                    'currentAmount' => $playthroughRule->getCurrentAmount(),
                    'isActive' => $playthroughRule->isActive(),
                    'message' => 'Counter incremented successfully',
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
