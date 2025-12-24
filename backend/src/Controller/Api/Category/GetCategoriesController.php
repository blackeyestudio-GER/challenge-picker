<?php

namespace App\Controller\Api\Category;

use App\DTO\Response\Category\CategoryResponse;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/categories', name: 'api_categories_list', methods: ['GET'])]
class GetCategoriesController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    /**
     * Get all game categories.
     *
     * @return JsonResponse List of categories
     */
    public function __invoke(): JsonResponse
    {
        try {
            $categories = $this->categoryRepository->findAllOrdered();

            $categoryResponses = array_map(
                fn ($category) => CategoryResponse::fromEntity($category),
                $categories
            );

            return $this->json([
                'success' => true,
                'data' => $categoryResponses,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_CATEGORIES_FAILED',
                    'message' => 'Failed to fetch categories',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
