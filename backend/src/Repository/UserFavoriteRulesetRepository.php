<?php

namespace App\Repository;

use App\Entity\UserFavoriteRuleset;
use App\Entity\User;
use App\Entity\Ruleset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserFavoriteRuleset>
 */
class UserFavoriteRulesetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFavoriteRuleset::class);
    }

    public function findFavorite(User $user, Ruleset $ruleset): ?UserFavoriteRuleset
    {
        return $this->findOneBy([
            'user' => $user,
            'ruleset' => $ruleset
        ]);
    }

    public function getFavoriteRulesetIds(User $user): array
    {
        $result = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.ruleset) as rulesetId')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return array_column($result, 'rulesetId');
    }
}
