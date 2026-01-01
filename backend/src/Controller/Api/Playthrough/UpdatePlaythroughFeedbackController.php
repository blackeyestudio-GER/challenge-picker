<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Request\Playthrough\UpdatePlaythroughFeedbackRequest;
use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdatePlaythroughFeedbackController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/api/playthroughs/{uuid}/feedback', name: 'api_playthrough_update_feedback', methods: ['PUT'])]
    public function __invoke(string $uuid, UpdatePlaythroughFeedbackRequest $request): JsonResponse
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

        // Validate request
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => implode(', ', $errorMessages),
                ],
            ], Response::HTTP_BAD_REQUEST);
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

        // Only allow feedback on completed playthroughs
        if ($playthrough->getStatus() !== \App\Entity\Playthrough::STATUS_COMPLETED) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_STATUS',
                    'message' => 'Feedback can only be set on completed playthroughs',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Update fields if provided
            if ($request->finishedRun !== null) {
                $playthrough->setFinishedRun($request->finishedRun);
            }
            if ($request->recommended !== null) {
                $playthrough->setRecommended($request->recommended);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => PlaythroughResponse::fromEntity($playthrough),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update playthrough feedback: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
