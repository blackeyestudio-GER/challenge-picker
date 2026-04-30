<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Response\Playthrough\PublicPlaythroughResponse;
use App\Entity\Playthrough;
use App\Repository\PlaythroughRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/playthrough/public/{uuid}', name: 'api_playthrough_public', methods: ['GET'])]
class GetPublicPlaythroughController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(string $uuid): JsonResponse
    {
        try {
            $playthroughUuid = Uuid::fromString($uuid);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid UUID format'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $playthroughUuid]);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Playthrough not found'],
            ], Response::HTTP_NOT_FOUND);
        }

        // Only show completed runs publicly
        if ($playthrough->getStatus() !== Playthrough::STATUS_COMPLETED) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'This playthrough is not yet completed'],
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json(PublicPlaythroughResponse::fromPlaythrough($playthrough), Response::HTTP_OK);
    }
}
