<?php

namespace App\Controller\Api\PlaythroughRule;

use App\Repository\PlaythroughRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DecrementCounterController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRuleRepository $playthroughRuleRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/playthrough/rules/{id}/decrement', name: 'api_playthrough_rule_decrement', methods: ['POST'])]
    public function __invoke(int $id): JsonResponse
    {
        try {
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

            // Verify user owns this playthrough
            $playthrough = $playthroughRule->getPlaythrough();
            if (!$playthrough || !$playthrough->getUser()->getUuid()->equals($user->getUuid())) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'You do not own this playthrough',
                    ],
                ], Response::HTTP_FORBIDDEN);
            }

            // Check if playthrough is active
            if ($playthrough->getStatus() !== 'active') {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'SESSION_NOT_ACTIVE',
                        'message' => 'Session must be active to decrement counters',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $currentAmount = $playthroughRule->getCurrentAmount();
            if ($currentAmount === null || $currentAmount <= 0) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_AMOUNT',
                        'message' => 'Counter is already at zero or not a counter rule',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            // Decrement counter
            $newAmount = $currentAmount - 1;
            $playthroughRule->setCurrentAmount($newAmount);

            // If counter reaches 0, deactivate the rule and mark as completed (for breathing room)
            if ($newAmount <= 0) {
                $playthroughRule->setIsActive(false);
                $playthroughRule->setCompletedAt(new \DateTimeImmutable());
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $playthroughRule->getId(),
                    'currentAmount' => $playthroughRule->getCurrentAmount(),
                    'isActive' => $playthroughRule->isActive(),
                    'message' => $newAmount <= 0 ? 'Counter completed - rule deactivated' : 'Counter decremented successfully',
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
