<?php

namespace App\Controller\Api\Shop;

use App\Repository\DesignSetRepository;
use App\Repository\UserDesignSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/shop/design-sets', name: 'api_shop_design_sets', methods: ['GET'])]
class ListDesignSetsController extends AbstractController
{
    public function __construct(
        private readonly DesignSetRepository $designSetRepository,
        private readonly UserDesignSetRepository $userDesignSetRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Get all design sets, ordered by sort_order
        $designSets = $this->designSetRepository->findBy([], ['sortOrder' => 'ASC']);

        $data = [];
        foreach ($designSets as $designSet) {
            $owned = false;
            if ($user) {
                $owned = $this->userDesignSetRepository->userOwnsDesignSet(
                    $user->getUuid(),
                    $designSet->getId()
                );
            }

            $data[] = [
                'id' => $designSet->getId(),
                'name' => $designSet->getName(),
                'type' => $designSet->getType(),
                'is_premium' => $designSet->getIsPremium(),
                'price' => $designSet->getPrice(),
                'theme' => $designSet->getTheme(),
                'description' => $designSet->getDescription(),
                'owned' => $owned,
            ];
        }

        return $this->json([
            'success' => true,
            'data' => ['design_sets' => $data],
        ]);
    }
}
