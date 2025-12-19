<?php

namespace App\Controller\Api\Obs;

use App\DTO\Request\Obs\UpdateObsPreferenceRequest;
use App\DTO\Response\Obs\ObsPreferenceResponse;
use App\Service\ObsPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateObsPreferencesController extends AbstractController
{
    public function __construct(
        private ObsPreferenceService $obsPreferenceService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/api/users/me/obs-preferences', name: 'update_obs_preferences', methods: ['PUT'])]
    public function __invoke(Request $request): JsonResponse
    {
        // Get authenticated user
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => ['message' => 'Unauthorized']
            ], 401);
        }

        // Deserialize and validate request
        $updateRequest = $this->serializer->deserialize(
            $request->getContent(),
            UpdateObsPreferenceRequest::class,
            'json'
        );

        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Validation failed',
                    'details' => (string) $errors
                ]
            ], 400);
        }

        $preferences = $this->obsPreferenceService->updatePreferences($user, $updateRequest);

        return new JsonResponse(
            ObsPreferenceResponse::fromEntity($preferences),
            200
        );
    }
}

