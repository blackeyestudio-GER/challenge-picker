<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Request\Playthrough\UpdatePlaythroughPrivacyRequest;
use App\DTO\Response\Playthrough\UpdatePlaythroughPrivacyResponse;
use App\Entity\User;
use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/playthrough/privacy', name: 'api_playthrough_update_privacy', methods: ['PATCH'])]
#[IsGranted('ROLE_USER')]
class UpdatePlaythroughPrivacyController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(UpdatePlaythroughPrivacyRequest $request): JsonResponse
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

        $requireAuth = $request->requireAuth;
        if ($requireAuth === null) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'requireAuth field is required',
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
        $playthrough->setRequireAuth($requireAuth);
        $this->entityManager->flush();

        return $this->json(
            UpdatePlaythroughPrivacyResponse::fromValue($playthrough->isRequireAuth()),
            Response::HTTP_OK
        );
    }
}
