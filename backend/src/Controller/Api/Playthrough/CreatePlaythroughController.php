<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Request\Playthrough\CreatePlaythroughRequest;
use App\DTO\Response\Playthrough\CreatePlaythroughResponse;
use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Service\PlaythroughService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreatePlaythroughController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughService $playthroughService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/api/playthroughs', name: 'api_playthrough_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        // Get authenticated user
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Deserialize and validate request
        $createRequest = $this->serializer->deserialize(
            $request->getContent(),
            CreatePlaythroughRequest::class,
            'json'
        );

        $errors = $this->validator->validate($createRequest);
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
                    'details' => $errorMessages
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $playthrough = $this->playthroughService->createPlaythrough(
                $user,
                $createRequest->gameId,
                $createRequest->rulesetId,
                $createRequest->maxConcurrentRules
            );

            $playthroughResponse = PlaythroughResponse::fromEntity($playthrough);
            $response = CreatePlaythroughResponse::fromPlaythrough($playthroughResponse);

            return $this->json($response, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_ERROR',
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

