<?php

namespace App\Controller\Api\Playthrough;

use App\Entity\User;
use App\Repository\PlaythroughRepository;
use App\Service\PlaythroughService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeletePlaythroughController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly PlaythroughService $playthroughService
    ) {
    }

    #[Route('/api/playthroughs/{uuid}', name: 'api_playthrough_delete', methods: ['DELETE'])]
    public function __invoke(string $uuid): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $playthrough = $this->playthroughRepository->findByUuid($uuid);
        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->playthroughService->deleteShortCompletedPlaythrough($playthrough, $user);

            return $this->json([
                'success' => true,
                'data' => [
                    'uuid' => $uuid,
                    'message' => 'Playthrough deleted',
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $statusCode = $message === 'You do not have access to this playthrough'
                ? Response::HTTP_FORBIDDEN
                : Response::HTTP_BAD_REQUEST;

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_ERROR',
                    'message' => $message,
                ],
            ], $statusCode);
        }
    }
}
