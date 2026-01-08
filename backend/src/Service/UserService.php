<?php

namespace App\Service;

use App\DTO\Request\User\CreateUserRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailService $emailService,
    ) {
    }

    /**
     * Create a new user.
     *
     * @throws \Exception if user already exists
     */
    public function createUser(CreateUserRequest $request): User
    {
        // Check if email already exists
        if ($this->userRepository->findByEmail($request->email)) {
            throw new \Exception('User with this email already exists');
        }

        // Check if username already exists
        if ($this->userRepository->findByUsername($request->username)) {
            throw new \Exception('Username is already taken');
        }

        // Create new user entity
        $user = new User();
        $user->setEmail($request->email);
        $user->setUsername($request->username);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $request->password
        );
        $user->setPassword($hashedPassword);

        // Generate email verification token
        $verificationToken = bin2hex(random_bytes(32));
        $verificationExpiresAt = new \DateTimeImmutable('+24 hours');
        $user->setEmailVerificationToken($verificationToken);
        $user->setEmailVerificationTokenExpiresAt($verificationExpiresAt);
        $user->setEmailVerified(false);

        // Persist to database
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Send verification email
        try {
            $this->emailService->sendEmailVerificationEmail($user->getEmail(), $verificationToken);
        } catch (\Exception $e) {
            // Log error but don't fail registration
            error_log('Failed to send verification email: ' . $e->getMessage());
        }

        return $user;
    }

    /**
     * Get user by UUID.
     *
     * @throws \Exception if user not found
     */
    public function getUserByUuid(string $uuid): User
    {
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return $user;
    }

    /**
     * Update user profile.
     *
     * @throws \Exception if email/username already exists
     */
    public function updateProfile(string $userUuid, string $email, string $username, ?string $avatar): User
    {
        $user = $this->getUserByUuid($userUuid);

        // Check if email changed and is taken by another user
        if ($email !== $user->getEmail()) {
            $existingUser = $this->userRepository->findByEmail($email);
            if ($existingUser && $existingUser->getUuid()->toRfc4122() !== $userUuid) {
                throw new \Exception('Email is already taken');
            }
            $user->setEmail($email);
        }

        // Check if username changed and is taken by another user
        if ($username !== $user->getUsername()) {
            $existingUser = $this->userRepository->findByUsername($username);
            if ($existingUser && $existingUser->getUuid()->toRfc4122() !== $userUuid) {
                throw new \Exception('Username is already taken');
            }
            $user->setUsername($username);
        }

        // Update avatar if provided
        if ($avatar !== null) {
            $user->setAvatar($avatar);
        }

        $this->entityManager->flush();

        return $user;
    }

    /**
     * Update user password.
     *
     * @throws \Exception if current password is incorrect
     */
    public function updatePassword(string $userUuid, string $currentPassword, string $newPassword): User
    {
        $user = $this->getUserByUuid($userUuid);

        // Verify current password
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            throw new \Exception('Current password is incorrect');
        }

        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();

        return $user;
    }
}
