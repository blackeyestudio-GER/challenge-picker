<?php

namespace App\Controller\Api\Challenge;

use App\DTO\Request\Challenge\SendChallengeRequest;
use App\Entity\Challenge;
use App\Entity\User;
use App\Repository\ChallengeRepository;
use App\Repository\PlaythroughRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/challenges/send', name: 'api_challenge_send', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class SendChallengeController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly ChallengeRepository $challengeRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] SendChallengeRequest $request,
        Request $httpRequest
    ): JsonResponse {
        /** @var User $challenger */
        $challenger = $this->getUser();

        // Find the playthrough
        $playthroughUuid = Uuid::fromString($request->playthroughUuid);
        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $playthroughUuid]);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify the challenger owns this playthrough
        if ($playthrough->getUser()->getUuid()->toRfc4122() !== $challenger->getUuid()->toRfc4122()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'You can only challenge others with your own playthrough',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Find the challenged user by Discord ID or email
        $challengedUser = $this->findUserByIdentifier($request->challengedUserIdentifier);

        if (!$challengedUser) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'User not found with the provided identifier',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Cannot challenge yourself
        if ($challengedUser->getUuid()->toRfc4122() === $challenger->getUuid()->toRfc4122()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CANNOT_CHALLENGE_SELF',
                    'message' => 'You cannot challenge yourself',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if challenged user already has an active playthrough
        $existingPlaythrough = $this->playthroughRepository->findActiveByUser($challengedUser->getUuid());
        if ($existingPlaythrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_HAS_ACTIVE_PLAYTHROUGH',
                    'message' => 'This user already has an active playthrough',
                ],
            ], Response::HTTP_CONFLICT);
        }

        // Check if a pending challenge already exists
        $hasPendingChallenge = $this->challengeRepository->hasPendingChallenge(
            $challenger->getUuid(),
            $challengedUser->getUuid(),
            $playthrough->getId(),
            $playthrough->getUser()->getUuid()
        );

        if ($hasPendingChallenge) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CHALLENGE_ALREADY_EXISTS',
                    'message' => 'You have already challenged this user with this playthrough',
                ],
            ], Response::HTTP_CONFLICT);
        }

        // Create the challenge
        $challenge = new Challenge();
        $challenge->setChallenger($challenger);
        $challenge->setChallengedUser($challengedUser);
        $challenge->setSourcePlaythrough($playthrough);

        $this->entityManager->persist($challenge);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'challengeUuid' => $challenge->getUuid()->toRfc4122(),
                'message' => 'Challenge sent successfully',
            ],
        ], Response::HTTP_CREATED);
    }

    private function findUserByIdentifier(string $identifier): ?User
    {
        // Try to find by Discord ID first
        $user = $this->userRepository->findOneBy(['discordId' => $identifier]);

        if ($user) {
            return $user;
        }

        // Try to find by email
        return $this->userRepository->findOneBy(['email' => $identifier]);
    }
}
