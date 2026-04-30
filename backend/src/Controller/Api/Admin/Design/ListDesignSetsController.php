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

        /** @var \App\Entity\DesignSet $designSet */
        $data = array_map(function ($designSet) {
            $completedCount = 0;

            foreach ($designSet->getCardDesigns() as $cardDesign) {
                if ($cardDesign->getImageBase64() !== null) {
                    ++$completedCount;
                }
            }

            $previewImages = $designSet->collectPreviewImageBase64s(4);
            $previewImage = $previewImages[0] ?? null;

            $expectedCardCount = $designSet->isTemplate() ? 3 : 78;
            $designName = $designSet->getDesignName();
            if (!$designName) {
                return null;
            }

            return [
                'id' => $designSet->getId(),
                'designNameId' => $designName->getId(),
                'designName' => $designName->getName(),
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
                'previewImages' => $previewImages,
                'createdAt' => $designSet->getCreatedAt()->format('c'),
                'updatedAt' => $designSet->getUpdatedAt()->format('c'),
            ];
        }, $designSets);
        $data = array_filter($data, fn ($item) => $item !== null);

        return $this->json([
            'success' => true,
            'data' => ['designSets' => $data],
        ], Response::HTTP_OK);
    }
}
