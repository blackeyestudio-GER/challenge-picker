<?php

namespace App\Controller\Api\Challenge;

use App\Entity\User;
use App\Repository\ChallengeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/challenges/sent', name: 'api_challenges_sent', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class GetSentChallengesController extends AbstractController
{
    public function __construct(
        private readonly ChallengeRepository $challengeRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // Get all challenges sent by the user
        $sentChallenges = $this->challengeRepository->findChallengesSentByUser($user->getUuid());

        // Group challenges by source playthrough UUID
        $challengesByPlaythrough = [];
        
        foreach ($sentChallenges as $challenge) {
            $sourcePlaythrough = $challenge->getSourcePlaythrough();
            $playthroughUuid = $sourcePlaythrough->getUuid()->toRfc4122();
            
            if (!isset($challengesByPlaythrough[$playthroughUuid])) {
                $game = $sourcePlaythrough->getGame();
                $ruleset = $sourcePlaythrough->getRuleset();
                
                $challengesByPlaythrough[$playthroughUuid] = [
                    'playthroughUuid' => $playthroughUuid,
                    'game' => [
                        'id' => $game?->getId(),
                        'name' => $game?->getName() ?? 'Unknown',
                        'imageBase64' => $game?->getImageBase64(),
                    ],
                    'ruleset' => [
                        'id' => $ruleset?->getId(),
                        'name' => $ruleset?->getName() ?? 'Unknown',
                    ],
                    'createdAt' => $sourcePlaythrough->getCreatedAt()->format('c'),
                    'challenges' => [],
                ];
            }
            
            $challengedUser = $challenge->getChallengedUser();
            $resultingPlaythrough = $challenge->getResultingPlaythrough();
            
            $challengesByPlaythrough[$playthroughUuid]['challenges'][] = [
                'uuid' => $challenge->getUuid()->toRfc4122(),
                'challengedUser' => [
                    'uuid' => $challengedUser->getUuid()->toRfc4122(),
                    'username' => $challengedUser->getUsername(),
                ],
                'status' => $challenge->getStatus(),
                'createdAt' => $challenge->getCreatedAt()->format('c'),
                'respondedAt' => $challenge->getRespondedAt()?->format('c'),
                'expiresAt' => $challenge->getExpiresAt()->format('c'),
                'resultingPlaythroughUuid' => $resultingPlaythrough?->getUuid()->toRfc4122(),
            ];
        }

        // Convert to array and sort by creation date (newest first)
        $groupedChallenges = array_values($challengesByPlaythrough);
        usort($groupedChallenges, function ($a, $b) {
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });

        return $this->json([
            'success' => true,
            'data' => [
                'challenges' => $groupedChallenges,
                'totalCount' => count($sentChallenges),
                'playthroughCount' => count($groupedChallenges),
            ],
        ], Response::HTTP_OK);
    }
}

