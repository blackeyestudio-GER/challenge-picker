<?php

namespace App\Controller\Api\User;

use App\DTO\Request\User\DeleteAccountRequest;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DeleteAccountController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    #[Route('/api/users/me', name: 'api_user_delete_account', methods: ['DELETE'])]
    public function __invoke(
        #[CurrentUser] ?User $user,
        #[MapRequestPayload] DeleteAccountRequest $request
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'User must be logged in',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->userService->deleteAccount($user, $request->currentPassword);

            return $this->json([
                'success' => true,
                'message' => 'Account deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_DELETE_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
