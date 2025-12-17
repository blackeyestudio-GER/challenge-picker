<?php

namespace App\Controller\Api\User;

use App\DTO\Request\User\UpdateProfileRequest;
use App\DTO\Response\User\UserResponse;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users/{id}', name: 'api_user_update_profile', methods: ['PUT'])]
class UpdateProfileController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Update user profile (email, username, avatar)
     * 
     * @param int $id User ID
     * @param UpdateProfileRequest $request Profile data
     * @return JsonResponse Updated user data
     */
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateProfileRequest $request
    ): JsonResponse {
        try {
            // Update profile
            $user = $this->userService->updateProfile(
                $id,
                $request->email,
                $request->username,
                $request->avatar
            );

            // Convert to response DTO
            $response = UserResponse::fromEntity($user);

            return $this->json([
                'success' => true,
                'data' => $response,
                'message' => 'Profile updated successfully'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PROFILE_UPDATE_FAILED',
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

