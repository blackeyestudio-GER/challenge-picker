<?php

namespace App\Controller\Api\Admin\Category;

use App\DTO\Request\Admin\UpdateCategoryRequest;
use App\DTO\Response\Admin\CategoryMutationResponse;
use App\DTO\Response\Category\CategoryResponse;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/categories/{id}', name: 'api_admin_categories_update', methods: ['PUT'])]
class UpdateAdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly GameRepository $gameRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
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

            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = UpdateCategoryRequest::fromArray($payloadData);
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

            if ($payload->name !== null) {
                $category->setName($payload->name);
                $slug = strtolower(str_replace([' ', ':', '&'], ['-', '', 'and'], $payload->name));
                $category->setSlug($slug);
            }
            if ($payload->hasDescription) {
                $category->setDescription($payload->description);
            }

            if ($payload->hasGameIds) {
                foreach ($category->getGames() as $game) {
                    $category->removeGame($game);
                }

                foreach ($payload->gameIds ?? [] as $gameId) {
                    $game = $this->gameRepository->find($gameId);
                    if ($game) {
                        $category->addGame($game);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json(
                CategoryMutationResponse::fromValues('Category updated successfully', CategoryResponse::fromEntity($category)),
                Response::HTTP_OK
            );

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
