<?php

namespace App\Controller\Api\Admin\Design;

use App\DTO\Response\Admin\DesignSetListItem;
use App\DTO\Response\Admin\DesignSetsResponse;
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

            $previewImages = $designSet->collectPreviewImageBase64s(4);
            $previewImage = $previewImages[0] ?? null;

            $expectedCardCount = $designSet->isTemplate() ? 3 : 78;
            $designName = $designSet->getDesignName();
            if (!$designName) {
                return null;
            }

            $designNameId = $designName->getId();
            $designNameValue = $designName->getName();
            if ($designSet->getId() === null || $designNameId === null) {
                return null;
            }

            return new DesignSetListItem(
                id: $designSet->getId(),
                designNameId: $designNameId,
                designName: $designNameValue,
                type: $designSet->getType(),
                isFree: $designSet->isFree(),
                isPremium: $designSet->isPremium(),
                price: $designSet->getPrice(),
                theme: $designSet->getTheme(),
                description: $designSet->getDescription(),
                cardCount: $expectedCardCount,
                completedCards: $completedCount,
                isComplete: $completedCount === $expectedCardCount,
                previewImage: $previewImage,
                previewImages: $previewImages,
                createdAt: $designSet->getCreatedAt()->format('c'),
                updatedAt: $designSet->getUpdatedAt()->format('c')
            );
        }, $designSets);
        $data = array_values(array_filter($data, fn ($item) => $item instanceof DesignSetListItem));

        return $this->json(DesignSetsResponse::fromItems($data), Response::HTTP_OK);
    }
}
