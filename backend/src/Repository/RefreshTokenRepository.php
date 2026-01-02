<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findByToken(string $token): ?RefreshToken
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * Find all refresh tokens for a user.
     *
     * @return RefreshToken[]
     */
    public function findByUser(Uuid $userUuid): array
    {
        return $this->createQueryBuilder('rt')
            ->join('rt.user', 'u')
            ->where('u.uuid = :userUuid')
            ->setParameter('userUuid', $userUuid, 'uuid')
            ->orderBy('rt.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Delete all expired refresh tokens.
     */
    public function deleteExpired(): int
    {
        return $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    /**
     * Delete all refresh tokens for a user.
     */
    public function deleteByUser(Uuid $userUuid): int
    {
        return $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.user = :userUuid')
            ->setParameter('userUuid', $userUuid, 'uuid')
            ->getQuery()
            ->execute();
    }

    /**
     * Delete a specific refresh token.
     */
    public function deleteByToken(string $token): int
    {
        return $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->execute();
    }
}
