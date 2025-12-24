<?php

namespace App\Controller\Api\Admin\Design;

use App\Repository\DesignSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/design-sets', name: 'api_admin_design_sets_list', methods: ['GET'])]
class ListDesignSetsController extends AbstractController
{
    public function __construct(
        private readonly DesignSetRepository $designSetRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $designSets = $this->designSetRepository->findAllWithDesignName();

        $data = array_map(function ($designSet) {
            $completedCount = 0;
            foreach ($designSet->getCardDesigns() as $cardDesign) {
                if ($cardDesign->getImageBase64() !== null) {
                    ++$completedCount;
                }
            }

            return [
                'id' => $designSet->getId(),
                'designNameId' => $designSet->getDesignName()->getId(),
                'designName' => $designSet->getDesignName()->getName(),
                'cardCount' => 78,
                'completedCards' => $completedCount,
                'isComplete' => $completedCount === 78,
                'createdAt' => $designSet->getCreatedAt()->format('c'),
                'updatedAt' => $designSet->getUpdatedAt()->format('c'),
            ];
        }, $designSets);

        return $this->json([
            'success' => true,
            'data' => ['designSets' => $data],
        ], Response::HTTP_OK);
    }
}
