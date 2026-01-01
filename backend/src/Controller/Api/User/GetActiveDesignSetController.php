<?php

namespace App\Controller\Api\User;

use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users/me/active-design-set', name: 'api_user_active_design_set', methods: ['GET'])]
class GetActiveDesignSetController extends AbstractController
{
    public function __construct(
        private readonly UserDesignSetRepository $userDesignSetRepository,
        private readonly DesignSetRepository $designSetRepository
    ) {
    }

    public function __invoke(): JsonResponse
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

        // Get user's owned design sets, ordered by most recent purchase
        $userDesignSets = $this->userDesignSetRepository->findByUser($user->getUuid());

        $activeDesignSet = null;

        if (count($userDesignSets) > 0) {
            // Use most recently purchased design set
            $activeDesignSet = $userDesignSets[0]->getDesignSet();
        } else {
            // Fallback: Use first free design set (if any)
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

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $activeDesignSet->getId(),
                'name' => $activeDesignSet->getName(),
                'type' => $activeDesignSet->getType(),
                'isPremium' => $activeDesignSet->getIsPremium(),
            ],
        ], Response::HTTP_OK);
    }
}
