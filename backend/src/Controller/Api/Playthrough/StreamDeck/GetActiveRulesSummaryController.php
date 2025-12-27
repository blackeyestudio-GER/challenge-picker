<?php

namespace App\Controller\Api\Playthrough\StreamDeck;

use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/stream-deck/rules-summary', name: 'api_playthrough_stream_deck_rules_summary', methods: ['GET'])]
class GetActiveRulesSummaryController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(): Response
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return new Response('Not logged in', Response::HTTP_UNAUTHORIZED, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            $playthrough = $this->playthroughRepository->findOneBy([
                'user' => $user,
                'status' => 'active',
            ]);

            if (!$playthrough) {
                return new Response('No active run', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            $activeRules = $playthrough->getPlaythroughRules()->filter(
                function (\App\Entity\PlaythroughRule $pr): bool {
                    return $pr->isActive() === true;
                }
            );

            $totalActive = $activeRules->count();
            $permanentCount = 0;
            $timerCount = 0;
            $counterCount = 0;

            foreach ($activeRules as $playthroughRule) {
                $hasTimer = $playthroughRule->getExpiresAt() !== null;
                $hasCounter = $playthroughRule->getCurrentAmount() !== null;

                if (!$hasTimer && !$hasCounter) {
                    ++$permanentCount;
                } elseif ($hasTimer) {
                    ++$timerCount;
                } elseif ($hasCounter) {
                    ++$counterCount;
                }
            }

            $text = sprintf(
                "📋 Active Rules: %d\n🔮 Permanent: %d\n⏱️ Timers: %d\n🔢 Counters: %d",
                $totalActive,
                $permanentCount,
                $timerCount,
                $counterCount
            );

            return new Response($text, Response::HTTP_OK, [
                'Content-Type' => 'text/plain; charset=utf-8',
            ]);
        } catch (\Exception $e) {
            return new Response('Error', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'Content-Type' => 'text/plain',
            ]);
        }
    }
}
