<?php

namespace App\Service;

use App\DTO\Request\Auth\LoginRequest;
use App\DTO\Response\Auth\LoginResponse;
use App\DTO\Response\User\UserResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * Authenticate user and generate JWT token.
     *
     * @throws \Exception if authentication fails
     */
    public function login(LoginRequest $request): LoginResponse
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($request->email);

        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        // Check if user has password (not OAuth-only user)
        if (!$user->isPasswordUser()) {
            throw new \Exception('This account uses OAuth authentication. Please login with ' . $user->getOauthProvider());
        }

        // Verify password
        if (!$this->passwordHasher->isPasswordValid($user, $request->password)) {
            throw new \Exception('Invalid credentials');
        }

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        // Get token TTL (default 3600 seconds = 1 hour)
        $expiresIn = 3600;

        return new LoginResponse(
            token: $token,
            user: UserResponse::fromEntity($user),
            expiresIn: $expiresIn,
        );
    }

    /**
     * Get user from JWT token.
     */
    public function getUserFromToken(string $token): ?User
    {
        try {
            $payload = $this->jwtManager->parse($token);
            $username = $payload['username'] ?? null;

            if (!$username) {
                return null;
            }

            return $this->userRepository->findByEmail($username);
        } catch (\Exception $e) {
            return null;
        }
    }
}
