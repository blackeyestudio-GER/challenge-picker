<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Request\Playthrough\UpdateMaxConcurrentRequest;
use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Repository\PlaythroughRepository;
use App\Service\PlaythroughService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateMaxConcurrentRulesController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly PlaythroughService $playthroughService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/api/playthroughs/{uuid}/concurrent', name: 'api_playthrough_concurrent_update', methods: ['PUT'])]
    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        // Get authenticated user
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

        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $uuid]);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify the playthrough belongs to the authenticated user
        if ($playthrough->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You do not have access to this playthrough',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Deserialize and validate request
        $updateRequest = $this->serializer->deserialize(
            $request->getContent(),
            UpdateMaxConcurrentRequest::class,
            'json'
        );

        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $errorMessages,
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $playthrough = $this->playthroughService->updateMaxConcurrent(
                $playthrough,
                $updateRequest->maxConcurrentRules
            );

            $playthroughResponse = PlaythroughResponse::fromEntity($playthrough);

            return $this->json([
                'success' => true,
                'data' => $playthroughResponse,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
