<?php

namespace App\Controller\Api\Admin\Game;

use App\DTO\Response\Game\GameResponse;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/games/{id}', name: 'api_admin_games_update', methods: ['PUT'])]
class UpdateAdminGameController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $game = $this->gameRepository->find($id);

            if (!$game) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'GAME_NOT_FOUND',
                        'message' => 'Game not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['name'])) {
                $game->setName($data['name']);
            }
            if (array_key_exists('description', $data)) {
                $game->setDescription($data['description']);
            }
            if (array_key_exists('image', $data)) {
                $game->setImage($data['image']);
            }
            if (isset($data['isCategoryRepresentative'])) {
                $game->setIsCategoryRepresentative($data['isCategoryRepresentative']);
            }
            if (array_key_exists('steamLink', $data)) {
                $game->setSteamLink($data['steamLink']);
            }
            if (array_key_exists('epicLink', $data)) {
                $game->setEpicLink($data['epicLink']);
            }
            if (array_key_exists('gogLink', $data)) {
                $game->setGogLink($data['gogLink']);
            }
            if (array_key_exists('twitchCategory', $data)) {
                $game->setTwitchCategory($data['twitchCategory']);
            }

            // Handle category associations
            if (array_key_exists('categoryIds', $data)) {
                // Clear existing categories
                foreach ($game->getCategories() as $category) {
                    $game->removeCategory($category);
                }

                // Add new categories
                if (is_array($data['categoryIds'])) {
                    foreach ($data['categoryIds'] as $categoryId) {
                        $category = $this->categoryRepository->find($categoryId);
                        if ($category) {
                            $game->addCategory($category);
                        }
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Game updated successfully',
                'data' => ['game' => GameResponse::fromEntity($game)],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to update game: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update game',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
