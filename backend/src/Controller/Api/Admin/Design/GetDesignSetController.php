<?php

namespace App\Controller\Api\Admin\Design;

use App\Repository\DesignSetRepository;
use App\Repository\TarotCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/design-sets/{id}', name: 'api_admin_design_sets_get', methods: ['GET'])]
class GetDesignSetController extends AbstractController
{
    public function __construct(
        private readonly DesignSetRepository $designSetRepository,
        private readonly TarotCardRepository $tarotCardRepository
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        try {
            $designSet = $this->designSetRepository->findWithCardDesigns($id);
            
            if (!$designSet) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DESIGN_SET_NOT_FOUND',
                        'message' => 'Design set not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            // Get all tarot card info from database
            $tarotCards = [];
            foreach ($this->tarotCardRepository->findAllOrdered() as $tarotCard) {
                $tarotCards[$tarotCard->getIdentifier()] = $tarotCard;
            }

            $cards = [];
            $completedCount = 0;

            foreach ($designSet->getCardDesigns() as $cardDesign) {
                $hasImage = $cardDesign->getImageBase64() !== null;
                if ($hasImage) {
                    $completedCount++;
                }

                $identifier = $cardDesign->getCardIdentifier();
                $tarotCard = $tarotCards[$identifier] ?? null;

                $cards[] = [
                    'id' => $cardDesign->getId(),
                    'cardIdentifier' => $identifier,
                    'displayName' => $tarotCard?->getDisplayName() ?? str_replace('_', ' ', $identifier),
                    'imageBase64' => $cardDesign->getImageBase64(),
                    'hasImage' => $hasImage,
                    'rarity' => $tarotCard?->getRarity() ?? 'common',
                    'sortOrder' => $tarotCard?->getSortOrder() ?? 999, // Used for sorting only
                    'updatedAt' => $cardDesign->getUpdatedAt()->format('c')
                ];
            }

            // Sort cards by sortOrder from database
            usort($cards, function($a, $b) {
                return $a['sortOrder'] <=> $b['sortOrder'];
            });

            // Remove sortOrder from response (only needed for sorting)
            foreach ($cards as &$card) {
                unset($card['sortOrder']);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'designSet' => [
                        'id' => $designSet->getId(),
                        'designNameId' => $designSet->getDesignName()->getId(),
                        'designName' => $designSet->getDesignName()->getName(),
                        'cardCount' => count($cards),
                        'completedCards' => $completedCount,
                        'isComplete' => $completedCount === 78,
                        'cards' => $cards,
                        'createdAt' => $designSet->getCreatedAt()->format('c'),
                        'updatedAt' => $designSet->getUpdatedAt()->format('c')
                    ]
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            error_log('Failed to fetch design set: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch design set'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

