<?php

namespace App\Controller\Api\User;

use App\DTO\Request\User\UpdatePasswordRequest;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users/{uuid}/password', name: 'api_user_update_password', methods: ['PUT'])]
class UpdatePasswordController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Update user password
     * 
     * @param string $uuid User UUID
     * @param UpdatePasswordRequest $request Password data
     * @return JsonResponse Success message
     */
    public function __invoke(
        string $uuid,
        #[MapRequestPayload] UpdatePasswordRequest $request
    ): JsonResponse {
        try {
            // Update password
            $this->userService->updatePassword(
                $uuid,
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

