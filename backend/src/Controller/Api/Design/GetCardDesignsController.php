<?php

namespace App\Controller\Api\Design;

use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use App\Repository\UserRepository;
use App\Service\CardDesignService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/design/card-designs', name: 'api_design_card_designs', methods: ['GET'])]
class GetCardDesignsController extends AbstractController
{
    public function __construct(
        private readonly UserDesignSetRepository $userDesignSetRepository,
        private readonly DesignSetRepository $designSetRepository,
        private readonly CardDesignService $cardDesignService,
        private readonly UserRepository $userRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
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

        // Check if we should fetch designs for a specific user (e.g., playthrough host)
        // Authentication is optional - endpoint works for anonymous viewers too
        $authenticatedUser = $this->getUser();
        $targetUser = null;
        $userUuidParam = $request->query->get('userUuid');

        if ($userUuidParam) {
            // Fetch designs for specific user (e.g., playthrough host)
            try {
                $targetUserUuid = Uuid::fromString($userUuidParam);
                $targetUser = $this->userRepository->findOneBy(['uuid' => $targetUserUuid]);
            } catch (\Exception $e) {
                // Invalid UUID format, ignore
            }
        } elseif ($authenticatedUser) {
            // Use authenticated user's designs if no specific user requested
            $targetUser = $authenticatedUser;
        }

        // Get target user's active design set (if user found)
        $activeDesignSet = null;

        if ($targetUser) {
            try {
                $userDesignSets = $this->userDesignSetRepository->findByUser($targetUser->getUuid());
                if (count($userDesignSets) > 0) {
                    $activeDesignSet = $userDesignSets[0]->getDesignSet();
                }
            } catch (\Exception $e) {
                // User might not have any design sets, continue to fallback
                error_log('Error fetching user design sets: ' . $e->getMessage());
            }
        }

        // Fallback to first free design set if no user design set found
        if (!$activeDesignSet) {
            try {
                $freeDesignSets = $this->designSetRepository->findBy(['isFree' => true], ['id' => 'ASC']);
                if (count($freeDesignSets) > 0) {
                    $activeDesignSet = $freeDesignSets[0];
                }
            } catch (\Exception $e) {
                // No free design sets available
            }
        }

        // If still no design set, return empty card designs (text-only mode)
        if (!$activeDesignSet) {
            $cardDesigns = [];
            foreach ($cardIdentifiers as $identifier) {
                $cardDesigns[$identifier] = null;
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'designSetId' => null,
                    'designSetName' => 'Text Only',
                    'cardDesigns' => $cardDesigns,
                ],
            ], Response::HTTP_OK);
        }

        // Get card designs for each identifier
        $cardDesigns = [];
        foreach ($cardIdentifiers as $identifier) {
            try {
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
            } catch (\Exception $e) {
                // If card design fetch fails, set to null (text-only mode for this card)
                $cardDesigns[$identifier] = null;
            }
        }

        try {
            $designSetName = 'Unknown';
            if ($activeDesignSet->getDesignName()) {
                $designSetName = $activeDesignSet->getDesignName()->getName() ?? 'Unknown';
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'designSetId' => $activeDesignSet->getId(),
                    'designSetName' => $designSetName,
                    'cardDesigns' => $cardDesigns,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Fallback if there's any error accessing design set properties
            return $this->json([
                'success' => true,
                'data' => [
                    'designSetId' => null,
                    'designSetName' => 'Unknown',
                    'cardDesigns' => $cardDesigns,
                ],
            ], Response::HTTP_OK);
        }
    }
}
