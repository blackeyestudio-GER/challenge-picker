<?php

namespace App\Controller\Api\Admin\Design;

use App\DTO\Request\Admin\UpdateDesignSetRequest;
use App\DTO\Response\Admin\DesignSetListItem;
use App\DTO\Response\Admin\DesignSetMutationResponse;
use App\Repository\DesignSetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/design-sets/{id}', name: 'api_admin_design_sets_update', methods: ['PUT', 'PATCH'])]
class UpdateDesignSetController extends AbstractController
{
    public function __construct(
        private readonly DesignSetRepository $designSetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $designSet = $this->designSetRepository->find($id);
            if (!$designSet) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DESIGN_SET_NOT_FOUND',
                        'message' => 'Design set not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = UpdateDesignSetRequest::fromArray($payloadData);
            $errors = $this->validator->validate($payload);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => (string) $errors,
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($payload->type !== null && $payload->type !== $designSet->getType()) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'TYPE_IMMUTABLE',
                        'message' => 'Design set type cannot be changed after creation (templates have 3 cards, full sets have 78)',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $designName = $designSet->getDesignName();
            if ($payload->name !== null && $designName) {
                $designName->setName($payload->name);
            }

            if ($payload->hasDescription) {
                $designSet->setDescription($payload->description);
            }

            if ($payload->hasTheme) {
                $designSet->setTheme($payload->theme);
            }

            if ($payload->isFree !== null) {
                $designSet->setIsFree($payload->isFree);
            }

            if ($payload->hasPrice) {
                $price = is_numeric($payload->price) ? (float) $payload->price : null;
                $designSet->setPrice($price !== null ? (string) $price : null);
            }

            $isFree = $payload->isFree ?? $designSet->isFree();
            $price = $payload->hasPrice
                ? (is_numeric($payload->price) ? (float) $payload->price : null)
                : (is_numeric($designSet->getPrice()) ? (float) $designSet->getPrice() : null);
            $isPremium = !$isFree && $price !== null && $price > 0;
            $designSet->setIsPremium($isPremium);

            $this->entityManager->flush();

            $designSetId = $designSet->getId();
            $designNameId = $designName?->getId();
            $designNameValue = $designName?->getName();
            if ($designSetId === null || $designNameId === null || $designNameValue === null) {
                throw new \RuntimeException('Design set is missing required data');
            }

            $completedCount = 0;
            foreach ($designSet->getCardDesigns() as $cardDesign) {
                if ($cardDesign->getImageBase64() !== null) {
                    ++$completedCount;
                }
            }

            $previewImages = $designSet->collectPreviewImageBase64s(4);

            return $this->json(
                DesignSetMutationResponse::fromValues(
                    'Design set updated successfully',
                    new DesignSetListItem(
                        id: $designSetId,
                        designNameId: $designNameId,
                        designName: $designNameValue,
                        type: $designSet->getType(),
                        isFree: $designSet->isFree(),
                        isPremium: $designSet->isPremium(),
                        price: $designSet->getPrice(),
                        theme: $designSet->getTheme(),
                        description: $designSet->getDescription(),
                        cardCount: $designSet->isTemplate() ? 3 : 78,
                        completedCards: $completedCount,
                        isComplete: $completedCount === ($designSet->isTemplate() ? 3 : 78),
                        previewImage: $previewImages[0] ?? null,
                        previewImages: $previewImages,
                        createdAt: $designSet->getCreatedAt()->format('c'),
                        updatedAt: $designSet->getUpdatedAt()->format('c')
                    )
                ),
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            error_log('Failed to update design set: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update design set',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
