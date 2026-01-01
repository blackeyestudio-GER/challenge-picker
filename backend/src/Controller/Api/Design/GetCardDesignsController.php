<?php

namespace App\Controller\Api\Design;

use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use App\Service\CardDesignService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/design/card-designs', name: 'api_design_card_designs', methods: ['GET'])]
class GetCardDesignsController extends AbstractController
{
    public function __construct(
        private readonly UserDesignSetRepository $userDesignSetRepository,
        private readonly DesignSetRepository $designSetRepository,
        private readonly CardDesignService $cardDesignService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Get tarot card identifiers from query parameter (comma-separated)
        $cardIdentifiersParam = $request->query->get('identifiers', '');
        if (empty($cardIdentifiersParam)) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_PARAMETER',
                    'message' => 'identifiers parameter is required',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $cardIdentifiers = array_filter(array_map('trim', explode(',', $cardIdentifiersParam)));

        // Get user's active design set (most recent purchase or first free)
        $userDesignSets = $this->userDesignSetRepository->findByUser($user->getUuid());
        $activeDesignSet = null;

        if (count($userDesignSets) > 0) {
            $activeDesignSet = $userDesignSets[0]->getDesignSet();
        } else {
            $freeDesignSets = $this->designSetRepository->findBy(['isPremium' => false], ['sortOrder' => 'ASC']);
            if (count($freeDesignSets) > 0) {
                $activeDesignSet = $freeDesignSets[0];
            }
        }

        if (!$activeDesignSet) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_DESIGN_SET',
                    'message' => 'No design set available',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Get card designs for each identifier
        $cardDesigns = [];
        foreach ($cardIdentifiers as $identifier) {
            $cardDesign = $this->cardDesignService->getCardDesignForTarotCard($activeDesignSet, $identifier);

            if ($cardDesign) {
                $cardDesigns[$identifier] = [
                    'id' => $cardDesign->getId(),
                    'cardIdentifier' => $cardDesign->getCardIdentifier(),
                    'imageBase64' => $cardDesign->getImageBase64(),
                    'isTemplate' => $cardDesign->isTemplate(),
                    'templateType' => $cardDesign->getTemplateType(),
                ];
            } else {
                $cardDesigns[$identifier] = null;
            }
        }

        return $this->json([
            'success' => true,
            'data' => [
                'designSetId' => $activeDesignSet->getId(),
                'designSetName' => $activeDesignSet->getName(),
                'cardDesigns' => $cardDesigns,
            ],
        ], Response::HTTP_OK);
    }
}
