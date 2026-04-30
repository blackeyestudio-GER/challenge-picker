<?php

namespace App\Controller\Api\Challenge;

use App\DTO\Response\Challenge\ChallengeGameData;
use App\DTO\Response\Challenge\ChallengeItem;
use App\DTO\Response\Challenge\ChallengeListResponse;
use App\DTO\Response\Challenge\ChallengePlaythroughData;
use App\DTO\Response\Challenge\ChallengeRulesetData;
use App\DTO\Response\Challenge\ChallengeUserData;
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
            /** @var \App\Entity\Challenge $challenge */
            $sourcePlaythrough = $challenge->getSourcePlaythrough();
            $ruleset = $sourcePlaythrough->getRuleset();

            if (!$ruleset) {
                return null;
            }

            $rulesetId = $ruleset->getId();
            $rulesetName = $ruleset->getName();
            if ($rulesetId === null || $rulesetName === null) {
                return null;
            }

            $game = (function () use ($ruleset) {
                $games = $ruleset->getGames();
                $game = $games->isEmpty() ? null : $games->first();
                if (!$game) {
                    return null;
                }

                return new ChallengeGameData(
                    id: $game->getId(),
                    name: $game->getName(),
                    imageBase64: $game->getImage()
                );
            })();

            return new ChallengeItem(
                uuid: $challenge->getUuid()->toRfc4122(),
                challenger: new ChallengeUserData(
                    uuid: $challenge->getChallenger()->getUuid()->toRfc4122(),
                    username: $challenge->getChallenger()->getUsername(),
                    displayName: $challenge->getChallenger()->getUsername()
                ),
                playthrough: new ChallengePlaythroughData(
                    uuid: $sourcePlaythrough->getUuid()->toRfc4122(),
                    ruleset: new ChallengeRulesetData(
                        id: $rulesetId,
                        name: $rulesetName,
                        game: $game
                    ),
                    maxConcurrentRules: $sourcePlaythrough->getMaxConcurrentRules()
                ),
                createdAt: $challenge->getCreatedAt()->format('c'),
                expiresAt: $challenge->getExpiresAt()->format('c')
            );
        }, $pendingChallenges);
        $challenges = array_values(array_filter($challenges, fn ($item) => $item !== null));

        return $this->json(ChallengeListResponse::fromItems($challenges), Response::HTTP_OK);
    }
}
