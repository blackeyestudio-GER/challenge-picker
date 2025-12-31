<?php

namespace App\Controller\Api\User;

use App\DTO\Request\User\UpdateThemeRequest;
use App\DTO\Response\User\UserResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/users/me/theme', name: 'api_user_update_theme', methods: ['PUT'])]
class UpdateThemeController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Update user theme preference.
     *
     * @param UpdateThemeRequest $request Theme data
     * @param User|null $user Current authenticated user
     *
     * @return JsonResponse Updated user data
     */
    public function __invoke(
        #[MapRequestPayload] UpdateThemeRequest $request,
        #[CurrentUser] ?User $user = null
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'User must be logged in',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // Update theme
            $user->setTheme($request->theme);
            $this->entityManager->flush();

            // Convert to response DTO
            $response = UserResponse::fromEntity($user);

            return $this->json([
                'success' => true,
                'data' => $response,
                'message' => 'Theme updated successfully',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'THEME_UPDATE_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

