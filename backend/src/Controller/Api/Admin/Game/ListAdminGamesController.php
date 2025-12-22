<?php

namespace App\Controller\Api\Admin\Game;

use App\DTO\Response\Game\GameResponse;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/games', name: 'api_admin_games_list', methods: ['GET'])]
class ListAdminGamesController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(200, (int) $request->query->get('limit', 50))); // Max 200 per page
        $search = $request->query->get('search', '');

        $offset = ($page - 1) * $limit;

        if (!empty($search)) {
            // Search mode: return all matching games (with pagination)
            $games = $this->gameRepository->searchGames($search, $limit, $offset);
            $total = $this->gameRepository->countSearchResults($search);
        } else {
            // Browse mode: return paginated games (active first, then inactive)
            $games = $this->gameRepository->findAllOrdered($limit, $offset);
            $total = $this->gameRepository->count([]);
        }

        $gameResponses = array_map(
            fn($game) => GameResponse::fromEntity($game),
            $games
        );

        return $this->json([
            'success' => true,
            'data' => [
                'games' => $gameResponses,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => (int) ceil($total / $limit)
                ]
            ]
        ], Response::HTTP_OK);
    }
}

