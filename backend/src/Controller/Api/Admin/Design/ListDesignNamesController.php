<?php

namespace App\Controller\Api\Admin\Design;

use App\Repository\DesignNameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/design-names', name: 'api_admin_design_names_list', methods: ['GET'])]
class ListDesignNamesController extends AbstractController
{
    public function __construct(
        private readonly DesignNameRepository $designNameRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $designNames = $this->designNameRepository->findAllOrdered();

        $data = array_map(function ($designName) {
            return [
                'id' => $designName->getId(),
                'name' => $designName->getName(),
                'description' => $designName->getDescription(),
                'createdAt' => $designName->getCreatedAt()->format('c'),
                'designSetCount' => $designName->getDesignSets()->count(),
            ];
        }, $designNames);

        return $this->json([
            'success' => true,
            'data' => ['designNames' => $data],
        ], Response::HTTP_OK);
    }
}
