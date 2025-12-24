<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserObsPreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserObsPreference>
 */
class UserObsPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserObsPreference::class);
    }

    public function findByUser(User $user): ?UserObsPreference
    {
        return $this->findOneBy(['user' => $user]);
    }

    public function save(UserObsPreference $preference, bool $flush = true): void
    {
        $this->getEntityManager()->persist($preference);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
