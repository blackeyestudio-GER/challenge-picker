<?php

namespace App\Controller\Api\Challenge;

use App\DTO\Response\Challenge\SentChallengeGameData;
use App\DTO\Response\Challenge\SentChallengeGroup;
use App\DTO\Response\Challenge\SentChallengeItem;
use App\DTO\Response\Challenge\SentChallengesResponse;
use App\DTO\Response\Challenge\SentChallengeRulesetData;
use App\DTO\Response\Challenge\SentChallengeUserData;
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
        try {
            /** @var User $user */
            $user = $this->getUser();

            // Get all challenges sent by the user
            $sentChallenges = $this->challengeRepository->findChallengesSentByUser($user->getUuid());

            // Group challenges by source playthrough UUID
            $challengesByPlaythrough = [];

            foreach ($sentChallenges as $challenge) {
                try {
                    $sourcePlaythrough = $challenge->getSourcePlaythrough();
                    $playthroughUuid = $sourcePlaythrough->getUuid()->toRfc4122();

                    if (!isset($challengesByPlaythrough[$playthroughUuid])) {
                        $game = $sourcePlaythrough->getGame();
                        $ruleset = $sourcePlaythrough->getRuleset();

                        $challengesByPlaythrough[$playthroughUuid] = [
                            'playthroughUuid' => $playthroughUuid,
                            'game' => new SentChallengeGameData(
                                id: $game?->getId(),
                                name: $game?->getName() ?? 'Unknown',
                                imageBase64: $game?->getImage()
                            ),
                            'ruleset' => new SentChallengeRulesetData(
                                id: $ruleset?->getId(),
                                name: $ruleset?->getName() ?? 'Unknown'
                            ),
                            'createdAt' => $sourcePlaythrough->getCreatedAt()->format('c'),
                            'challenges' => [],
                        ];
                    }

                    $challengedUser = $challenge->getChallengedUser();
                    $resultingPlaythrough = $challenge->getResultingPlaythrough();

                    $challengesByPlaythrough[$playthroughUuid]['challenges'][] = new SentChallengeItem(
                        uuid: $challenge->getUuid()->toRfc4122(),
                        challengedUser: new SentChallengeUserData(
                            uuid: $challengedUser->getUuid()->toRfc4122(),
                            username: $challengedUser->getUsername()
                        ),
                        status: $challenge->getStatus(),
                        createdAt: $challenge->getCreatedAt()->format('c'),
                        respondedAt: $challenge->getRespondedAt()?->format('c'),
                        expiresAt: $challenge->getExpiresAt()->format('c'),
                        resultingPlaythroughUuid: $resultingPlaythrough?->getUuid()?->toRfc4122()
                    );
                } catch (\Exception $e) {
                    // Log error but continue processing other challenges
                    error_log('Error processing challenge: ' . $e->getMessage());
                    continue;
                }
            }

            // Convert to array and sort by creation date (newest first)
            $groupedChallenges = array_map(
                static fn (array $group): SentChallengeGroup => new SentChallengeGroup(
                    playthroughUuid: $group['playthroughUuid'],
                    game: $group['game'],
                    ruleset: $group['ruleset'],
                    createdAt: $group['createdAt'],
                    challenges: $group['challenges']
                ),
                array_values($challengesByPlaythrough)
            );
            usort($groupedChallenges, function ($a, $b) {
                return strtotime($b->createdAt) - strtotime($a->createdAt);
            });

            return $this->json(SentChallengesResponse::fromGroups($groupedChallenges), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Log the full exception for debugging
            error_log('GetSentChallengesController error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch sent challenges: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
