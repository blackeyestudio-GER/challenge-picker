<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Request\Playthrough\AddVideoUrlRequest;
use App\DTO\Response\Playthrough\AddVideoUrlResponse;
use App\Entity\Playthrough;
use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/playthrough/{uuid}/video-url', name: 'api_playthrough_add_video_url', methods: ['PUT'])]
class AddVideoUrlController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(string $uuid, AddVideoUrlRequest $request): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
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
                'error' => ['message' => 'Playthrough not found'],
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify ownership
        if ($playthrough->getUser()->getUuid()->toRfc4122() !== $user->getUuid()->toRfc4122()) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Unauthorized'],
            ], Response::HTTP_FORBIDDEN);
        }

        // Only allow adding video to completed playthroughs
        if ($playthrough->getStatus() !== Playthrough::STATUS_COMPLETED) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Can only add video URL to completed playthroughs'],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($request->videoUrl === null) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Missing required field: videoUrl',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $videoUrl = trim($request->videoUrl);

        // Allow empty string to remove video URL
        if ($videoUrl === '') {
            $playthrough->setVideoUrl(null);
            $this->entityManager->flush();

            return $this->json(
                AddVideoUrlResponse::fromValues('Video URL removed successfully', null),
                Response::HTTP_OK
            );
        }

        // Validate URL format
        if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid URL format'],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate it's a YouTube or Twitch URL
        $isValidPlatform = $this->isYouTubeUrl($videoUrl) || $this->isTwitchUrl($videoUrl);

        if (!$isValidPlatform) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Only YouTube and Twitch URLs are allowed'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $playthrough->setVideoUrl($videoUrl);
        $this->entityManager->flush();

        return $this->json(
            AddVideoUrlResponse::fromValues('Video URL added successfully', $videoUrl),
            Response::HTTP_OK
        );
    }

    private function isYouTubeUrl(string $url): bool
    {
        $patterns = [
            '/^https?:\/\/(www\.)?youtube\.com\/watch\?v=[\w-]+/',
            '/^https?:\/\/youtu\.be\/[\w-]+/',
            '/^https?:\/\/(www\.)?youtube\.com\/embed\/[\w-]+/',
            '/^https?:\/\/(www\.)?youtube\.com\/v\/[\w-]+/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    private function isTwitchUrl(string $url): bool
    {
        $patterns = [
            '/^https?:\/\/(www\.)?twitch\.tv\/videos\/\d+/',
            '/^https?:\/\/(www\.)?twitch\.tv\/[\w-]+\/clip\/[\w-]+/',
            '/^https?:\/\/clips\.twitch\.tv\/[\w-]+/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }
}
