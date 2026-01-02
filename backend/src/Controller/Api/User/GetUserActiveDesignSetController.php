<?php

namespace App\Controller\Api\User;

use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/users/{userUuid}/active-design-set', name: 'api_user_uuid_active_design_set', methods: ['GET'])]
class GetUserActiveDesignSetController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserDesignSetRepository $userDesignSetRepository,
        private readonly DesignSetRepository $designSetRepository
    ) {
    }

    public function __invoke(string $userUuid): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($userUuid);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_UUID',
                    'message' => 'Invalid user UUID',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'User not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $activeDesignSet = null;

        // Get user's purchased design sets
        $userDesignSets = $this->userDesignSetRepository->findByUser($user->getUuid());
        $purchasedDesignSetIds = array_map(
            fn ($uds) => $uds->getDesignSet()?->getId(),
            array_filter($userDesignSets, fn ($uds) => $uds->getDesignSet() !== null)
        );

        // 1. Priority: Check user's explicitly selected design
        if ($user->getActiveDesignSetId()) {
            $selectedDesign = $this->designSetRepository->find($user->getActiveDesignSetId());

            // Verify user still has access (free OR purchased)
            if ($selectedDesign && ($selectedDesign->isFree() || in_array($selectedDesign->getId(), $purchasedDesignSetIds))) {
                $activeDesignSet = $selectedDesign;
            }
        }

        // 2. Fallback: Use most recently purchased design set
        if (!$activeDesignSet && count($userDesignSets) > 0) {
            $activeDesignSet = $userDesignSets[0]->getDesignSet();
        }

        // 3. Final fallback: Use first free design set (should be "Text Only")
        if (!$activeDesignSet) {
            $freeDesignSets = $this->designSetRepository->findBy(['isFree' => true], ['id' => 'ASC']);
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

        $designName = $activeDesignSet->getDesignName()?->getName() ?? 'Unknown';
        $theme = $activeDesignSet->getTheme() ?? '';

        // Determine display mode based on design set
        $displayIcon = false;
        $displayText = false;

        if ($theme === 'minimal' || stripos($designName, 'text only') !== false) {
            // Text Only: just text
            $displayText = true;
        } elseif ($theme === 'icon' || stripos($designName, 'icon only') !== false) {
            // Icon Only: just icon
            $displayIcon = true;
        } elseif ($theme === 'icon-text' || stripos($designName, 'icon + text') !== false || stripos($designName, 'icon and text') !== false) {
            // Icon + Text: both
            $displayIcon = true;
            $displayText = true;
        }

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $activeDesignSet->getId(),
                'name' => $designName,
                'type' => $activeDesignSet->getType(),
                'isPremium' => $activeDesignSet->isPremium(),
                'theme' => $theme,
                'displayIcon' => $displayIcon,
                'displayText' => $displayText,
            ],
        ], Response::HTTP_OK);
    }
}
