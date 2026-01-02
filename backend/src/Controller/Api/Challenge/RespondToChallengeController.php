<?php

namespace App\Controller\Api\Challenge;

use App\Entity\Challenge;
use App\Entity\User;
use App\Repository\ChallengeRepository;
use App\Repository\PlaythroughRepository;
use App\Service\PlaythroughService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/challenges/{uuid}/respond', name: 'api_challenge_respond', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class RespondToChallengeController extends AbstractController
{
    public function __construct(
        private readonly ChallengeRepository $challengeRepository,
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly PlaythroughService $playthroughService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // Find the challenge
        $challengeUuid = Uuid::fromString($uuid);
        $challenge = $this->challengeRepository->findOneBy(['uuid' => $challengeUuid]);

        if (!$challenge) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CHALLENGE_NOT_FOUND',
                    'message' => 'Challenge not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify the user is the challenged user
        if ($challenge->getChallengedUser()->getUuid()->toRfc4122() !== $user->getUuid()->toRfc4122()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'You are not authorized to respond to this challenge',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if challenge is still pending
        if (!$challenge->isPending()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CHALLENGE_NOT_PENDING',
                    'message' => 'This challenge is no longer pending',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get the action (accept or decline)
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => 'Invalid request body',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
        $action = $data['action'] ?? null;

        if (!in_array($action, ['accept', 'decline'])) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_ACTION',
                    'message' => 'Action must be either "accept" or "decline"',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($action === 'decline') {
            $challenge->setStatus(Challenge::STATUS_DECLINED);
            $challenge->setRespondedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'message' => 'Challenge declined',
                ],
            ], Response::HTTP_OK);
        }

        // Accept the challenge - check if user already has an active playthrough
        $existingPlaythrough = $this->playthroughRepository->findActiveByUser($user->getUuid());
        if ($existingPlaythrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_HAS_ACTIVE_PLAYTHROUGH',
                    'message' => 'You already have an active playthrough. Please complete or stop it first.',
                ],
            ], Response::HTTP_CONFLICT);
        }

        // Copy the playthrough configuration for the challenged user
        $sourcePlaythrough = $challenge->getSourcePlaythrough();

        try {
            $newPlaythrough = $this->playthroughService->createPlaythrough(
                $user,
                $sourcePlaythrough->getRuleset(),
                $sourcePlaythrough->getMaxConcurrentRules(),
                $sourcePlaythrough->isRequireAuth(),
                $sourcePlaythrough->isAllowViewerPicks(),
                $sourcePlaythrough->getConfiguration()
            );

            // Update challenge status
            $challenge->setStatus(Challenge::STATUS_ACCEPTED);
            $challenge->setRespondedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'message' => 'Challenge accepted',
                    'playthroughUuid' => $newPlaythrough->getUuid()->toRfc4122(),
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_CREATION_FAILED',
                    'message' => 'Failed to create playthrough: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
