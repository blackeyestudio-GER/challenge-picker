<?php

namespace App\Controller\Api\Challenge;

use App\Entity\User;
use App\Repository\ChallengeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/challenges/mine', name: 'api_challenges_mine', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class GetMyChallengesController extends AbstractController
{
    public function __construct(
        private readonly ChallengeRepository $challengeRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // Get all pending challenges for the user
        $pendingChallenges = $this->challengeRepository->findPendingChallengesForUser($user->getUuid());

        $challenges = array_map(function ($challenge) {
            $sourcePlaythrough = $challenge->getSourcePlaythrough();
            $ruleset = $sourcePlaythrough->getRuleset();

            return [
                'uuid' => $challenge->getUuid()->toRfc4122(),
                'challenger' => [
                    'uuid' => $challenge->getChallenger()->getUuid()->toRfc4122(),
                    'username' => $challenge->getChallenger()->getUsername(),
                    'displayName' => $challenge->getChallenger()->getUsername(),
                ],
                'playthrough' => [
                    'uuid' => $sourcePlaythrough->getUuid()->toRfc4122(),
                    'ruleset' => [
                        'id' => $ruleset->getId(),
                        'name' => $ruleset->getName(),
                        'difficulty' => $ruleset->getDifficulty(),
                        'game' => [
                            'id' => $ruleset->getGame()->getId(),
                            'name' => $ruleset->getGame()->getName(),
                            'imageBase64' => $ruleset->getGame()->getImageBase64(),
                        ],
                    ],
                    'maxConcurrentRules' => $sourcePlaythrough->getMaxConcurrentRules(),
                ],
                'createdAt' => $challenge->getCreatedAt()->format('c'),
                'expiresAt' => $challenge->getExpiresAt()->format('c'),
            ];
        }, $pendingChallenges);

        return $this->json([
            'success' => true,
            'data' => [
                'challenges' => $challenges,
                'count' => count($challenges),
            ],
        ], Response::HTTP_OK);
    }
}
