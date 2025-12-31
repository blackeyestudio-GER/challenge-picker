<?php

namespace App\Controller\Api\Features;

use App\Repository\FeatureSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/features/{featureKey}', name: 'api_features_check', methods: ['GET'])]
class CheckFeatureController extends AbstractController
{
    public function __construct(
        private readonly FeatureSettingsRepository $featureSettingsRepository
    ) {
    }

    public function __invoke(string $featureKey): JsonResponse
    {
        // Define defaults for each feature
        $defaults = [
            'browse_community_runs' => false,
            'shop' => true,
        ];

        // Validate feature key
        if (!isset($defaults[$featureKey])) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid feature key'],
            ], 400);
        }

        $setting = $this->featureSettingsRepository->findOneBy(['featureKey' => $featureKey]);
        $enabled = $setting ? $setting->isEnabled() : $defaults[$featureKey];

        return $this->json([
            'success' => true,
            'data' => [
                'feature' => $featureKey,
                'enabled' => $enabled,
            ],
        ]);
    }
}
