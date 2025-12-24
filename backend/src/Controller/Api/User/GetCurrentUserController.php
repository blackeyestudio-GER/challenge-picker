<?php

namespace App\Controller\Api\User;

use App\DTO\Response\User\UserResponse;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetCurrentUserController extends AbstractController
{
    #[Route('/api/users/me', name: 'api_user_me', methods: ['GET'])]
    public function __invoke(#[CurrentUser] ?User $user = null): JsonResponse
    {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'User must be logged in',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userResponse = UserResponse::fromEntity($user);

        return $this->json([
            'success' => true,
            'data' => $userResponse,
        ], Response::HTTP_OK);
    }
}
