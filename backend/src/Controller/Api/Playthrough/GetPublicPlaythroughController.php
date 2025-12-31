<?php

namespace App\Controller\Api\Playthrough;

use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/playthrough/public/{uuid}', name: 'api_playthrough_public', methods: ['GET'])]
class GetPublicPlaythroughController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(string $uuid): JsonResponse
    {
        try {
            $playthroughUuid = Uuid::fromString($uuid);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid UUID format'],
            ], 400);
        }

        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $playthroughUuid]);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Playthrough not found'],
            ], 404);
        }

        // Only show completed runs publicly
        if ($playthrough->getStatus() !== 'completed') {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'This playthrough is not yet completed'],
            ], 404);
        }

        $game = $playthrough->getGame();
        $ruleset = $playthrough->getRuleset();
        $user = $playthrough->getUser();

        // Get active rules from playthrough
        $activeRules = [];
        foreach ($playthrough->getPlaythroughRules() as $playthroughRule) {
            if ($playthroughRule->isActive()) {
                $rule = $playthroughRule->getRule();
                if ($rule) {
                    $activeRules[] = [
                        'id' => $rule->getId(),
                        'name' => $rule->getName(),
                        'description' => $rule->getDescription(),
                        'type' => $rule->getType(),
                    ];
                }
            }
        }

        return $this->json([
            'success' => true,
            'data' => [
                'playthrough' => [
                    'uuid' => $playthrough->getUuid()?->toRfc4122(),
                    'status' => $playthrough->getStatus(),
                    'startedAt' => $playthrough->getStartedAt()?->format('c'),
                    'endedAt' => $playthrough->getEndedAt()?->format('c'),
                    'totalDuration' => $playthrough->getTotalDuration(),
                    'videoUrl' => $playthrough->getVideoUrl(),
                    'game' => [
                        'id' => $game?->getId(),
                        'name' => $game?->getName(),
                        'imageUrl' => $game?->getImageUrl(),
                    ],
                    'ruleset' => [
                        'id' => $ruleset?->getId(),
                        'name' => $ruleset?->getName(),
                        'description' => $ruleset?->getDescription(),
                    ],
                    'user' => [
                        'username' => $user?->getUsername(),
                        'avatarUrl' => $user?->getAvatarUrl(),
                    ],
                    'activeRules' => $activeRules,
                ],
            ],
        ]);
    }
}
