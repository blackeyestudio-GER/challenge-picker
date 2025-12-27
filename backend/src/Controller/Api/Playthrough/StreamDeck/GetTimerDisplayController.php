<?php

namespace App\Controller\Api\Playthrough\StreamDeck;

use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/stream-deck/timer', name: 'api_playthrough_stream_deck_timer', methods: ['GET'])]
class GetTimerDisplayController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(Request $request): Response
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
                return new Response('No active timers', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            // Get index parameter (which timer to display: 1, 2, 3...)
            $index = (int) $request->query->get('index', 1);

            // Get all active time-based rules
            $activeRules = $playthrough->getPlaythroughRules()->filter(
                function (\App\Entity\PlaythroughRule $pr): bool {
                    return $pr->isActive() === true && $pr->getExpiresAt() !== null;
                }
            );

            if ($activeRules->isEmpty()) {
                return new Response('No active timers', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            // Convert to array and get by index
            $rulesArray = $activeRules->toArray();
            if (!isset($rulesArray[$index - 1])) {
                return new Response('Timer not found', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            $playthroughRule = $rulesArray[$index - 1];
            $rule = $playthroughRule->getRule();
            $expiresAt = $playthroughRule->getExpiresAt();

            if (!$expiresAt) {
                return new Response('No expiration', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            $now = new \DateTimeImmutable();
            $diff = $expiresAt->getTimestamp() - $now->getTimestamp();

            if ($diff <= 0) {
                return new Response('⏱️ EXPIRED', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $seconds = $diff % 60;

            if ($hours > 0) {
                $timeText = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            } else {
                $timeText = sprintf('%02d:%02d', $minutes, $seconds);
            }

            $text = sprintf(
                "⏱️ %s\n%s",
                $rule ? $rule->getName() : 'Timer',
                $timeText
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
