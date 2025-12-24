<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\UserFavoriteGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserFavoriteGame>
 */
class UserFavoriteGameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFavoriteGame::class);
    }

    public function findFavorite(User $user, Game $game): ?UserFavoriteGame
    {
        return $this->findOneBy([
            'user' => $user,
            'game' => $game,
        ]);
    }

    public function getFavoriteGameIds(User $user): array
    {
        $result = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.game) as gameId')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return array_column($result, 'gameId');
    }
}
