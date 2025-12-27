<?php

namespace App\Controller\Api\Playthrough\StreamDeck;

use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/stream-deck/status', name: 'api_playthrough_stream_deck_status', methods: ['GET'])]
class GetPlaythroughStatusController extends AbstractController
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

            $game = $playthrough->getGame();
            $ruleset = $playthrough->getRuleset();

            $text = sprintf(
                "🎮 %s\n📋 %s\n✅ Active",
                $game ? $game->getName() : 'Unknown',
                $ruleset ? $ruleset->getName() : 'Custom'
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
