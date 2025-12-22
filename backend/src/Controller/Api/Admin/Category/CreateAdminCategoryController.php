<?php

namespace App\Controller\Api\Admin\Category;

use App\DTO\Response\Category\CategoryResponse;
use App\Entity\Category;
use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/categories', name: 'api_admin_categories_create', methods: ['POST'])]
class CreateAdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameRepository $gameRepository
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $name = $data['name'] ?? null;
            if (!$name) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Name is required'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create slug from name
            $slug = strtolower(str_replace([' ', ':', '&'], ['-', '', 'and'], $name));
            
            // Create the category
            $category = new Category();
            $category->setName($name);
            $category->setSlug($slug);
            $category->setDescription($data['description'] ?? null);

            $this->entityManager->persist($category);
            $this->entityManager->flush();
            
            // Automatically create a representative game for this category
            $existingGame = $this->gameRepository->findOneBy([
                'name' => $name,
                'isCategoryRepresentative' => true
            ]);
            
            if (!$existingGame) {
                $game = new Game();
                $game->setName($name);
                $game->setDescription("Representative game for {$name} category");
                $game->setIsCategoryRepresentative(true);
                
                $this->entityManager->persist($game);
                $this->entityManager->flush();
                
                // Link game to category
                $this->entityManager->getConnection()->executeStatement(
                    'INSERT INTO game_categories (game_id, category_id, created_at) VALUES (?, ?, NOW())',
                    [$game->getId(), $category->getId()]
                );
            }

            return $this->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => ['category' => CategoryResponse::fromEntity($category)]
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            error_log('Failed to create category: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create category: ' . $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

