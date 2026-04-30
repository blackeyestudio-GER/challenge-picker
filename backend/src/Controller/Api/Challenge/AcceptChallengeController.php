<?php

namespace App\Controller\Api\Challenge;

use App\DTO\Response\Challenge\RespondToChallengeResponse;
use App\Entity\Challenge;
use App\Entity\User;
use App\Repository\ChallengeRepository;
use App\Repository\PlaythroughRepository;
use App\Service\PlaythroughService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/challenges/{uuid}/accept', name: 'api_challenge_accept', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class AcceptChallengeController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly ChallengeRepository $challengeRepository,
        private readonly PlaythroughService $playthroughService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // Find the source playthrough
        $playthroughUuid = Uuid::fromString($uuid);
        $sourcePlaythrough = $this->playthroughRepository->findOneBy(['uuid' => $playthroughUuid]);

        if (!$sourcePlaythrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Challenge not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if user already has an active playthrough
        $existingPlaythrough = $this->playthroughRepository->findActiveByUser($user);
        if ($existingPlaythrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_HAS_ACTIVE_PLAYTHROUGH',
                    'message' => 'You already have an active playthrough. Please complete or stop it first.',
                ],
            ], Response::HTTP_CONFLICT);
        }

        // Cannot accept your own challenge
        if ($sourcePlaythrough->getUser()->getUuid()->toRfc4122() === $user->getUuid()->toRfc4122()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CANNOT_ACCEPT_OWN_CHALLENGE',
                    'message' => 'You cannot accept your own challenge',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $game = $sourcePlaythrough->getGame();
        $ruleset = $sourcePlaythrough->getRuleset();
        if (null === $game || null === $ruleset) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_SOURCE_PLAYTHROUGH',
                    'message' => 'Source playthrough is missing game or ruleset',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $gameId = $game->getId();
        $rulesetId = $ruleset->getId();
        if (null === $gameId || null === $rulesetId) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_SOURCE_PLAYTHROUGH',
                    'message' => 'Source playthrough is missing game or ruleset id',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $newPlaythrough = $this->playthroughService->createPlaythrough(
                $user,
                $gameId,
                $rulesetId,
                $sourcePlaythrough->getMaxConcurrentRules(),
                $sourcePlaythrough->isRequireAuth(),
                $sourcePlaythrough->isAllowViewerPicks(),
                $sourcePlaythrough->getConfiguration()
            );

            $pendingChallenge = $this->challengeRepository->findPendingForChallengedUserAndSourcePlaythrough($user, $sourcePlaythrough);
            if (null !== $pendingChallenge) {
                $pendingChallenge->setStatus(Challenge::STATUS_ACCEPTED);
                $pendingChallenge->setRespondedAt(new \DateTimeImmutable());
                $pendingChallenge->setResultingPlaythrough($newPlaythrough);
                $this->entityManager->flush();
            }

            return $this->json(
                RespondToChallengeResponse::fromValues(
                    'Challenge accepted!',
                    $newPlaythrough->getUuid()->toRfc4122()
                ),
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_CREATION_FAILED',
                    'message' => 'Failed to accept challenge: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
