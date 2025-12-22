<?php

namespace App\Controller\Api\Admin\Category;

use App\DTO\Response\Category\CategoryResponse;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
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
    ) {}

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $category = $this->categoryRepository->find($id);
            
            if (!$category) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CATEGORY_NOT_FOUND',
                        'message' => 'Category not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['name'])) {
                $category->setName($data['name']);
                // Update slug as well
                $slug = strtolower(str_replace([' ', ':', '&'], ['-', '', 'and'], $data['name']));
                $category->setSlug($slug);
            }
            if (array_key_exists('description', $data)) {
                $category->setDescription($data['description']);
            }
            
            // Handle game associations
            if (array_key_exists('gameIds', $data)) {
                // Clear existing games
                foreach ($category->getGames() as $game) {
                    $category->removeGame($game);
                }
                
                // Add new games
                if (is_array($data['gameIds'])) {
                    foreach ($data['gameIds'] as $gameId) {
                        $game = $this->gameRepository->find($gameId);
                        if ($game) {
                            $category->addGame($game);
                        }
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => ['category' => CategoryResponse::fromEntity($category)]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            error_log('Failed to update category: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update category'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

