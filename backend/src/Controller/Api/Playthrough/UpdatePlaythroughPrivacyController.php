<?php

namespace App\Controller\Api\Playthrough;

use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/playthrough/privacy', name: 'api_playthrough_update_privacy', methods: ['PATCH'])]
#[IsGranted('ROLE_USER')]
class UpdatePlaythroughPrivacyController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
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

        // Get request data
        $data = json_decode($request->getContent(), true);
        if (!isset($data['requireAuth']) || !is_bool($data['requireAuth'])) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => 'requireAuth field is required and must be a boolean',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Find user's active playthrough
        $playthrough = $this->playthroughRepository->findActiveByUser($user);
        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_ACTIVE_PLAYTHROUGH',
                    'message' => 'No active playthrough found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Update privacy setting
        $playthrough->setRequireAuth($data['requireAuth']);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'requireAuth' => $playthrough->isRequireAuth(),
            ],
        ], Response::HTTP_OK);
    }
}
