<?php

namespace App\Controller\Api\Admin\Features;

use App\Entity\FeatureSettings;
use App\Repository\FeatureSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/features/settings', name: 'api_admin_features_settings_update', methods: ['PUT'])]
class UpdateFeatureSettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FeatureSettingsRepository $featureSettingsRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['featureKey'], $data['enabled'])) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Missing required fields: featureKey, enabled'],
            ], 400);
        }

        $featureKey = $data['featureKey'];
        $enabled = (bool) $data['enabled'];

        // Validate feature key
        $validKeys = ['browse_community_runs', 'shop'];
        if (!in_array($featureKey, $validKeys, true)) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid feature key'],
            ], 400);
        }

        // Get or create feature setting
        $setting = $this->featureSettingsRepository->findOneBy(['featureKey' => $featureKey]);

        if (!$setting) {
            $setting = new FeatureSettings();
            $setting->setFeatureKey($featureKey);
            $this->entityManager->persist($setting);
        }

        $setting->setEnabled($enabled);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'feature' => [
                    'key' => $setting->getFeatureKey(),
                    'enabled' => $setting->isEnabled(),
                ],
            ],
        ]);
    }
}
