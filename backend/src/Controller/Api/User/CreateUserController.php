<?php

namespace App\Controller\Api\User;

use App\DTO\Request\User\CreateUserRequest;
use App\DTO\Response\User\UserResponse;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', name: 'api_user_create', methods: ['POST'])]
class CreateUserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Create a new user
     * 
     * @param CreateUserRequest $request Validated request payload
     * @return JsonResponse User creation response
     */
    public function __invoke(
        #[MapRequestPayload] CreateUserRequest $request
    ): JsonResponse {
        try {
            // Create user through service
            $user = $this->userService->createUser($request);
            
            // Convert entity to response DTO
            $response = UserResponse::fromEntity($user);

            return $this->json([
                'success' => true,
                'data' => $response,
                'message' => 'User created successfully'
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_CREATION_FAILED',
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

