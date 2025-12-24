<?php

namespace App\Controller\Api\Admin\Category;

use App\DTO\Response\Category\CategoryResponse;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/categories', name: 'api_admin_categories_list', methods: ['GET'])]
class ListAdminCategoriesController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        try {
            $categories = $this->categoryRepository->findBy([], ['name' => 'ASC']);

            $categoryResponses = array_map(
                fn ($category) => CategoryResponse::fromEntity($category),
                $categories
            );

            return $this->json([
                'success' => true,
                'data' => ['categories' => $categoryResponses],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to fetch categories: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch categories',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
