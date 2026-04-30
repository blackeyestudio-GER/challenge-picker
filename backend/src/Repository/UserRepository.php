<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Find user by UUID.
     */
    public function findByUuid(string $uuid): ?User
    {
        try {
            $uuidObject = \Symfony\Component\Uid\Uuid::fromString($uuid);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        /** @var User|null $result */
        $result = $this->find($uuidObject);

        return $result;
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        /** @var User|null $result */
        $result = $this->findOneBy(['email' => $email]);

        return $result;
    }

    /**
     * Find user by username.
     */
    public function findByUsername(string $username): ?User
    {
        /** @var User|null $result */
        $result = $this->findOneBy(['username' => $username]);

        return $result;
    }

    /**
     * Find user by Discord ID.
     */
    public function findByDiscordId(string $discordId): ?User
    {
        /** @var User|null $result */
        $result = $this->findOneBy(['discordId' => $discordId]);

        return $result;
    }

    /**
     * Find user by Twitch ID.
     */
    public function findByTwitchId(string $twitchId): ?User
    {
        /** @var User|null $result */
        $result = $this->findOneBy(['twitchId' => $twitchId]);

        return $result;
    }

    /**
     * Find user by password reset token.
     */
    public function findByPasswordResetToken(string $token): ?User
    {
        /** @var User|null $result */
        $result = $this->findOneBy(['passwordResetToken' => $token]);

        return $result;
    }

    /**
     * Find user by email verification token.
     */
    public function findByEmailVerificationToken(string $token): ?User
    {
        /** @var User|null $result */
        $result = $this->findOneBy(['emailVerificationToken' => $token]);

        return $result;
    }
}
