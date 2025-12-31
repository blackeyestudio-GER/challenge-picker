<?php

namespace App\Controller\Api\Playthrough;

use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/{uuid}/video-url', name: 'api_playthrough_add_video_url', methods: ['PUT'])]
class AddVideoUrlController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $uuid]);

        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Playthrough not found'],
            ], 404);
        }

        // Verify ownership
        if ($playthrough->getUser()->getUuid()->toRfc4122() !== $user->getUuid()->toRfc4122()) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Unauthorized'],
            ], 403);
        }

        // Only allow adding video to completed playthroughs
        if ($playthrough->getStatus() !== 'completed') {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Can only add video URL to completed playthroughs'],
            ], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['videoUrl'])) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Missing required field: videoUrl'],
            ], 400);
        }

        $videoUrl = trim((string) $data['videoUrl']);

        // Allow empty string to remove video URL
        if ($videoUrl === '') {
            $playthrough->setVideoUrl(null);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => ['message' => 'Video URL removed successfully'],
            ]);
        }

        // Validate URL format
        if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Invalid URL format'],
            ], 400);
        }

        // Validate it's a YouTube or Twitch URL
        $isValidPlatform = $this->isYouTubeUrl($videoUrl) || $this->isTwitchUrl($videoUrl);

        if (!$isValidPlatform) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Only YouTube and Twitch URLs are allowed'],
            ], 400);
        }

        $playthrough->setVideoUrl($videoUrl);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'message' => 'Video URL added successfully',
                'videoUrl' => $videoUrl,
            ],
        ]);
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
