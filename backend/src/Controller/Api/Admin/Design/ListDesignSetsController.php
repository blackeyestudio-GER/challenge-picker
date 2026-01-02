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
            $previewImage = null;

            foreach ($designSet->getCardDesigns() as $cardDesign) {
                if ($cardDesign->getImageBase64() !== null) {
                    ++$completedCount;

                    // Get the first card with an image as preview
                    if ($previewImage === null) {
                        $previewImage = $cardDesign->getImageBase64();
                    }
                }
            }

            $expectedCardCount = $designSet->isTemplate() ? 3 : 78;

            return [
                'id' => $designSet->getId(),
                'designNameId' => $designSet->getDesignName()->getId(),
                'designName' => $designSet->getDesignName()->getName(),
                'type' => $designSet->getType(),
                'isFree' => $designSet->isFree(),
                'isPremium' => $designSet->isPremium(),
                'price' => $designSet->getPrice(),
                'theme' => $designSet->getTheme(),
                'description' => $designSet->getDescription(),
                'cardCount' => $expectedCardCount,
                'completedCards' => $completedCount,
                'isComplete' => $completedCount === $expectedCardCount,
                'previewImage' => $previewImage,
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
