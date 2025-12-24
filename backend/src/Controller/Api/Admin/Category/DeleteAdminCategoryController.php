<?php

namespace App\Controller\Api\Admin\Category;

use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/categories/{id}', name: 'api_admin_categories_delete', methods: ['DELETE'])]
class DeleteAdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id): JsonResponse
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

            $this->entityManager->remove($category);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to delete category: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Failed to delete category',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
