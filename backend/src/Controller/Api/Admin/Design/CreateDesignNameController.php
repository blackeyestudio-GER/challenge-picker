<?php

namespace App\Controller\Api\Admin\Design;

use App\Entity\DesignName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/design-names', name: 'api_admin_design_names_create', methods: ['POST'])]
class CreateDesignNameController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $designName = new DesignName();
            $designName->setName($data['name']);
            $designName->setDescription($data['description'] ?? null);

            $this->entityManager->persist($designName);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Design name created successfully',
                'data' => [
                    'designName' => [
                        'id' => $designName->getId(),
                        'name' => $designName->getName(),
                        'description' => $designName->getDescription(),
                        'createdAt' => $designName->getCreatedAt()->format('c'),
                        'designSetCount' => 0,
                    ],
                ],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            error_log('Failed to create design name: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create design name',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
