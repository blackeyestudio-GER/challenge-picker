<?php

namespace App\Controller\Api\Admin\Design;

use App\DTO\Response\Admin\DesignNameItem;
use App\DTO\Response\Admin\DesignNamesResponse;
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

        $data = [];
        foreach ($designNames as $designName) {
            $designNameId = $designName->getId();
            if ($designNameId === null) {
                continue;
            }

            $data[] = new DesignNameItem(
                id: $designNameId,
                name: $designName->getName(),
                description: $designName->getDescription(),
                createdAt: $designName->getCreatedAt()->format('c'),
                designSetCount: $designName->getDesignSets()->count()
            );
        }

        return $this->json(DesignNamesResponse::fromItems($data), Response::HTTP_OK);
    }
}
