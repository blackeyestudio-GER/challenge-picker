<?php

namespace App\Controller\Api\Admin\Category;

use App\DTO\Response\Category\CategoryResponse;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use App\Service\ArrayTypeHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/categories/{id}', name: 'api_admin_categories_update', methods: ['PUT'])]
class UpdateAdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly GameRepository $gameRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $category = $this->categoryRepository->find($id);

            if (!$category) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CATEGORY_NOT_FOUND',
                        'message' => 'Category not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_REQUEST',
                        'message' => 'Invalid request body',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            /** @var array<string, mixed> $data */
            if (isset($data['name'])) {
                $name = ArrayTypeHelper::getString($data, 'name');
                $category->setName($name);
                // Update slug as well
                $slug = strtolower(str_replace([' ', ':', '&'], ['-', '', 'and'], $name));
                $category->setSlug($slug);
            }
            if (array_key_exists('description', $data)) {
                $category->setDescription(ArrayTypeHelper::tryGetString($data, 'description'));
            }

            // Handle game associations
            if (array_key_exists('gameIds', $data)) {
                // Clear existing games
                foreach ($category->getGames() as $game) {
                    $category->removeGame($game);
                }

                // Add new games
                $gameIds = ArrayTypeHelper::tryGetArray($data, 'gameIds');
                if ($gameIds !== null) {
                    foreach ($gameIds as $gameId) {
                        if (is_int($gameId)) {
                            $game = $this->gameRepository->find($gameId);
                            if ($game) {
                                $category->addGame($game);
                            }
                        }
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => ['category' => CategoryResponse::fromEntity($category)],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to update category: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update category',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
