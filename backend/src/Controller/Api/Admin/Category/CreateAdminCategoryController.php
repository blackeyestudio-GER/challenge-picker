<?php

namespace App\Controller\Api\Admin\Category;

use App\DTO\Request\Admin\CreateCategoryRequest;
use App\DTO\Response\Admin\CategoryMutationResponse;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/categories', name: 'api_admin_categories_create', methods: ['POST'])]
class CreateAdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameRepository $gameRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = CreateCategoryRequest::fromArray($payloadData);
            $errors = $this->validator->validate($payload);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => (string) $errors,
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $slug = strtolower(str_replace([' ', ':', '&'], ['-', '', 'and'], $payload->name));

            $category = new Category();
            $category->setName($payload->name);
            $category->setSlug($slug);
            $category->setDescription($payload->description);

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            // Automatically create a representative game for this category
            $existingGame = $this->gameRepository->findOneBy([
                'name' => $payload->name,
                'isCategoryRepresentative' => true,
            ]);

            if (!$existingGame) {
                $game = new Game();
                $game->setName($payload->name);
                $game->setDescription(sprintf('Representative game for %s category', $payload->name));
                $game->setIsCategoryRepresentative(true);

                $this->entityManager->persist($game);
                $this->entityManager->flush();

                // Link game to category
                $this->entityManager->getConnection()->executeStatement(
                    'INSERT INTO game_categories (game_id, category_id, created_at) VALUES (?, ?, NOW())',
                    [$game->getId(), $category->getId()]
                );
            }

            return $this->json(
                CategoryMutationResponse::fromValues('Category created successfully', CategoryResponse::fromEntity($category)),
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            error_log('Failed to create category: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create category: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
