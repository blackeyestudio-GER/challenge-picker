<?php

namespace App\Controller\Api\Admin\Features;

use App\DTO\Request\Admin\UpdateFeatureSettingsRequest;
use App\DTO\Response\Admin\UpdateFeatureSettingsResponse;
use App\Entity\FeatureSettings;
use App\Repository\FeatureSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/features/settings', name: 'api_admin_features_settings_update', methods: ['PUT'])]
class UpdateFeatureSettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FeatureSettingsRepository $featureSettingsRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(UpdateFeatureSettingsRequest $request): JsonResponse
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'error' => ['message' => implode(', ', $messages)],
            ], Response::HTTP_BAD_REQUEST);
        }

        $featureKey = $request->featureKey;
        $enabled = $request->enabled;
        if ($featureKey === null || $enabled === null) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid feature settings payload'],
            ], Response::HTTP_BAD_REQUEST);
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

        $storedKey = $setting->getFeatureKey();
        $storedEnabled = $setting->isEnabled();
        if ($storedKey === null || $storedEnabled === null) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Failed to persist feature setting'],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(UpdateFeatureSettingsResponse::fromValues($storedKey, $storedEnabled), Response::HTTP_OK);
    }
}
