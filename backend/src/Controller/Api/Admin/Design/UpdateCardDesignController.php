<?php

namespace App\Controller\Api\Admin\Design;

use App\DTO\Request\Admin\UpdateCardDesignRequest;
use App\DTO\Response\Admin\CardDesignMutationItem;
use App\DTO\Response\Admin\CardDesignMutationResponse;
use App\Repository\CardDesignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/card-designs/{id}', name: 'api_admin_card_designs_update', methods: ['PUT'])]
class UpdateCardDesignController extends AbstractController
{
    public function __construct(
        private readonly CardDesignRepository $cardDesignRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $cardDesign = $this->cardDesignRepository->find($id);

            if (!$cardDesign) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CARD_DESIGN_NOT_FOUND',
                        'message' => 'Card design not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = UpdateCardDesignRequest::fromArray($payloadData);
            if ($payload->hasImageBase64) {
                $cardDesign->setImageBase64($payload->imageBase64);
            }

            $this->entityManager->flush();

            $cardDesignId = $cardDesign->getId();
            if ($cardDesignId === null) {
                throw new \RuntimeException('Card design is missing required data');
            }

            return $this->json(
                CardDesignMutationResponse::fromValues(
                    'Card design updated successfully',
                    new CardDesignMutationItem(
                        id: $cardDesignId,
                        cardIdentifier: $cardDesign->getCardIdentifier(),
                        hasImage: $cardDesign->getImageBase64() !== null,
                        updatedAt: $cardDesign->getUpdatedAt()->format('c')
                    )
                ),
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            error_log('Failed to update card design: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update card design',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
