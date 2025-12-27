<?php

namespace App\Controller\Api\Playthrough\StreamDeck;

use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/stream-deck/counter', name: 'api_playthrough_stream_deck_counter', methods: ['GET'])]
class GetCounterDisplayController extends AbstractController
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
                return new Response('No active counters', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            // Get index parameter (which counter to display: 1, 2, 3...)
            $index = (int) $request->query->get('index', 1);

            // Get all active counter-based rules
            $activeRules = $playthrough->getPlaythroughRules()->filter(
                function (\App\Entity\PlaythroughRule $pr): bool {
                    return $pr->isActive() === true && $pr->getCurrentAmount() !== null;
                }
            );

            if ($activeRules->isEmpty()) {
                return new Response('No active counters', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            // Convert to array and get by index
            $rulesArray = $activeRules->toArray();
            if (!isset($rulesArray[$index - 1])) {
                return new Response('Counter not found', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            $playthroughRule = $rulesArray[$index - 1];
            $rule = $playthroughRule->getRule();
            $currentAmount = $playthroughRule->getCurrentAmount();

            if ($currentAmount === null) {
                return new Response('No counter', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            if ($currentAmount <= 0) {
                return new Response('✅ COMPLETE', Response::HTTP_OK, [
                    'Content-Type' => 'text/plain',
                ]);
            }

            $text = sprintf(
                "🔢 %s\n%d remaining",
                $rule ? $rule->getName() : 'Counter',
                $currentAmount
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
