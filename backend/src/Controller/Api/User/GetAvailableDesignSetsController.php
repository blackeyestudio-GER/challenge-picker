<?php

namespace App\Controller\Api\User;

use App\Entity\DesignSet;
use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users/me/available-design-sets', name: 'api_user_available_design_sets', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class GetAvailableDesignSetsController extends AbstractController
{
    public function __construct(
        private readonly UserDesignSetRepository $userDesignSetRepository,
        private readonly DesignSetRepository $designSetRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
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

        // Get all free design sets
        $freeDesignSets = $this->designSetRepository->findBy(['isFree' => true], ['id' => 'ASC']);

        // Get user's purchased design sets
        $userDesignSets = $this->userDesignSetRepository->findByUser($user->getUuid());
        $purchasedDesignSets = array_filter(
            array_map(fn ($uds) => $uds->getDesignSet(), $userDesignSets),
            fn ($ds) => $ds !== null
        );

        // Combine and deduplicate (in case a user purchased a now-free set)
        $allDesignSets = [];
        $seenIds = [];

        foreach ($freeDesignSets as $designSet) {
            $allDesignSets[] = $designSet;
            $seenIds[$designSet->getId()] = true;
        }

        foreach ($purchasedDesignSets as $designSet) {
            $id = $designSet->getId();
            if ($id !== null && !isset($seenIds[$id])) {
                $allDesignSets[] = $designSet;
                $seenIds[$id] = true;
            }
        }

        // Format response
        $designs = array_map(function (DesignSet $designSet) {
            $designName = $designSet->getDesignName()?->getName() ?? 'Unknown';

            return [
                'id' => $designSet->getId(),
                'name' => $designName,
                'type' => $designSet->getType(),
                'isFree' => $designSet->isFree(),
                'isPremium' => $designSet->isPremium(),
                'description' => $designSet->getDescription(),
                'theme' => $designSet->getTheme(),
                'price' => $designSet->getPrice(),
            ];
        }, $allDesignSets);

        return $this->json([
            'success' => true,
            'data' => [
                'designSets' => $designs,
            ],
        ], Response::HTTP_OK);
    }
}
