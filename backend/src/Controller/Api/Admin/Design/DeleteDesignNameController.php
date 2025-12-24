<?php

namespace App\Controller\Api\Admin\Design;

use App\Repository\DesignNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/design-names/{id}', name: 'api_admin_design_names_delete', methods: ['DELETE'])]
class DeleteDesignNameController extends AbstractController
{
    public function __construct(
        private readonly DesignNameRepository $designNameRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $designName = $this->designNameRepository->find($id);

            if (!$designName) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DESIGN_NAME_NOT_FOUND',
                        'message' => 'Design name not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($designName);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Design name deleted successfully',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to delete design name: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_FAILED',
                    'message' => 'Failed to delete design name',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
