<?php

namespace App\Controller\Api\Artist;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/artist/apply', name: 'api_artist_apply', methods: ['POST'])]
class ApplyArtistController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        Request $request,
        #[CurrentUser] \App\Entity\User $user
    ): JsonResponse {
        // Check if user is already an artist
        if ($user->isArtist()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'ALREADY_ARTIST',
                    'message' => 'You are already an artist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_REQUEST',
                        'message' => 'Invalid request body',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $portfolioUrl = $data['portfolioUrl'] ?? null;

            // For now, auto-approve artists (can be changed to require admin approval)
            // In the future, you might want to add an application status field
            $user->setIsArtist(true);

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'message' => 'Artist application approved',
                    'isArtist' => true,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'APPLICATION_FAILED',
                    'message' => 'Failed to submit artist application',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
