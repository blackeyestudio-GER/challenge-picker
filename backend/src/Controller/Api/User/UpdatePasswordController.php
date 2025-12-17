<?php

namespace App\Controller\Api\User;

use App\DTO\Request\User\UpdatePasswordRequest;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users/{id}/password', name: 'api_user_update_password', methods: ['PUT'])]
class UpdatePasswordController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Update user password
     * 
     * @param int $id User ID
     * @param UpdatePasswordRequest $request Password data
     * @return JsonResponse Success message
     */
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdatePasswordRequest $request
    ): JsonResponse {
        try {
            // Update password
            $this->userService->updatePassword(
                $id,
                $request->currentPassword,
                $request->newPassword
            );

            return $this->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PASSWORD_UPDATE_FAILED',
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

