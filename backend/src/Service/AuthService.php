<?php

namespace App\Service;

use App\DTO\Request\Auth\LoginRequest;
use App\DTO\Response\Auth\LoginResponse;
use App\DTO\Response\User\UserResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Authenticate user and generate JWT token.
     *
     * @param bool $rememberMe If true, creates a 30-day refresh token
     *
     * @throws \Exception if authentication fails
     */
    public function login(LoginRequest $request, bool $rememberMe = false): LoginResponse
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

        // Get token TTL (configured to 86400 seconds = 24 hours for long streaming sessions)
        $expiresIn = 86400;

        // Create refresh token if "remember me" is checked
        $refreshToken = null;
        if ($rememberMe) {
            $refreshToken = $this->createRefreshToken($user);
        }

        return new LoginResponse(
            token: $token,
            user: UserResponse::fromEntity($user),
            expiresIn: $expiresIn,
            refreshToken: $refreshToken,
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

            if (!is_string($username) || $username === '') {
                return null;
            }

            return $this->userRepository->findByEmail($username);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create a refresh token for the user (30-day expiration).
     */
    private function createRefreshToken(User $user): string
    {
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));

        // Set expiration to 30 days from now
        $expiresAt = new \DateTimeImmutable('+30 days');

        $user->setRefreshToken($token);
        $user->setRefreshTokenExpiresAt($expiresAt);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $token;
    }

    /**
     * Refresh JWT token using a refresh token.
     */
    public function refreshToken(string $refreshTokenString): LoginResponse
    {
        // Find user by refresh token
        $user = $this->userRepository->findOneBy(['refreshToken' => $refreshTokenString]);

        if (!$user) {
            throw new \Exception('Invalid refresh token');
        }

        // Check if refresh token is expired
        $expiresAt = $user->getRefreshTokenExpiresAt();
        if (!$expiresAt || $expiresAt < new \DateTimeImmutable()) {
            throw new \Exception('Refresh token has expired');
        }

        // Generate new JWT token
        $token = $this->jwtManager->create($user);

        // Get token TTL
        $expiresIn = 86400; // 24 hours

        // Create a new refresh token (rotate tokens for security)
        $newRefreshToken = $this->createRefreshToken($user);

        return new LoginResponse(
            token: $token,
            user: UserResponse::fromEntity($user),
            expiresIn: $expiresIn,
            refreshToken: $newRefreshToken,
        );
    }
}
