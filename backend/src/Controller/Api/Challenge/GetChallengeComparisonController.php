<?php

namespace App\Controller\Api\Challenge;

use App\DTO\Response\Challenge\ChallengeComparisonData;
use App\DTO\Response\Challenge\ChallengeComparisonResponse;
use App\DTO\Response\Challenge\ParticipantData;
use App\Repository\ChallengeRepository;
use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/challenges/comparison/{playthroughUuid}', name: 'api_challenge_comparison', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class GetChallengeComparisonController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly ChallengeRepository $challengeRepository
    ) {
    }

    public function __invoke(string $playthroughUuid): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Find the source playthrough
        $sourcePlaythroughUuid = Uuid::fromString($playthroughUuid);
        $sourcePlaythrough = $this->playthroughRepository->findOneBy(['uuid' => $sourcePlaythroughUuid]);

        if (!$sourcePlaythrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify user owns this playthrough
        if ($sourcePlaythrough->getUser()->getUuid()->toRfc4122() !== $user->getUuid()->toRfc4122()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'You can only view comparisons for your own playthroughs',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Find all challenges from this playthrough
        $challenges = $this->challengeRepository->findChallengesBySourcePlaythrough(
            $sourcePlaythrough->getId(),
            $sourcePlaythrough->getUser()->getUuid()
        );

        // Build comparison data
        $response = new \App\DTO\Response\Challenge\ChallengeComparisonResponse();
        $data = new ChallengeComparisonData();
        
        $data->sourcePlaythroughUuid = $sourcePlaythrough->getUuid()->toRfc4122();
        $data->sourceUsername = $sourcePlaythrough->getUser()->getUsername();
        $data->gameName = $sourcePlaythrough->getGame()?->getName() ?? 'Unknown';
        $data->rulesetName = $sourcePlaythrough->getRuleset()?->getName() ?? 'Unknown';
        
        // Calculate source duration
        $data->sourceDuration = $this->calculateDuration($sourcePlaythrough);
        
        // Get source active rules
        $data->sourceActiveRules = $this->getActiveRules($sourcePlaythrough);

        // Process participants
        foreach ($challenges as $challenge) {
            $participant = new ParticipantData();
            $participant->username = $challenge->getChallengedUser()->getUsername();
            $participant->status = $challenge->getStatus();
            
            $resultingPlaythrough = $challenge->getResultingPlaythrough();
            if ($resultingPlaythrough) {
                $participant->playthroughUuid = $resultingPlaythrough->getUuid()->toRfc4122();
                $participant->duration = $this->calculateDuration($resultingPlaythrough);
                $participant->activeRules = $this->getActiveRules($resultingPlaythrough);
            } else {
                $participant->playthroughUuid = '';
                $participant->duration = null;
                $participant->activeRules = [];
            }
            
            $data->participants[] = $participant;
        }

        $response->data = $data;

        return $this->json($response, Response::HTTP_OK);
    }

    private function calculateDuration(\App\Entity\Playthrough $playthrough): ?int
    {
        if ($playthrough->getTotalDuration() !== null) {
            return $playthrough->getTotalDuration();
        }

        // Calculate from startedAt and endedAt if available
        $startedAt = $playthrough->getStartedAt();
        $endedAt = $playthrough->getEndedAt();

        if ($startedAt && $endedAt) {
            return $endedAt->getTimestamp() - $startedAt->getTimestamp();
        }

        // If still active, calculate from startedAt to now
        if ($startedAt && in_array($playthrough->getStatus(), [\App\Entity\Playthrough::STATUS_ACTIVE, \App\Entity\Playthrough::STATUS_PAUSED])) {
            $now = new \DateTimeImmutable();
            $totalPaused = $playthrough->getTotalPausedDuration() ?? 0;
            return $now->getTimestamp() - $startedAt->getTimestamp() - $totalPaused;
        }

        return null;
    }

    private function getActiveRules(Playthrough $playthrough): array
    {
        $activeRules = [];
        
        foreach ($playthrough->getPlaythroughRules() as $playthroughRule) {
            if ($playthroughRule->isActive() || $playthroughRule->getCompletedAt() !== null) {
                $rule = $playthroughRule->getRule();
                if ($rule) {
                    $activeRules[] = [
                        'ruleId' => $rule->getId(),
                        'ruleName' => $rule->getName(),
                        'ruleType' => $rule->getRuleType(),
                        'difficultyLevel' => $rule->getDifficultyLevel(),
                        'isActive' => $playthroughRule->isActive(),
                        'completed' => $playthroughRule->getCompletedAt() !== null,
                        'currentAmount' => $playthroughRule->getCurrentAmount(),
                        'startedAt' => $playthroughRule->getStartedAt()?->format('c'),
                        'completedAt' => $playthroughRule->getCompletedAt()?->format('c'),
                    ];
                }
            }
        }
        
        return $activeRules;
    }
}

