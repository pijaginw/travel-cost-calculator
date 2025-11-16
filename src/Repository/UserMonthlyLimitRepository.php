<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserMonthlyLimit;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<UserMonthlyLimit>
 */
class UserMonthlyLimitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMonthlyLimit::class);
    }

    /**
     * Find or create a monthly limit record for a user and month.
     *
     * @throws Exception
     */
    public function findOrCreate(User $user, DateTimeImmutable $month): UserMonthlyLimit
    {
        // Normalize to the first day of the month
        $normalizedMonth = new DateTimeImmutable($month->format('Y-m-01'));

        $limit = $this->findOneByUserAndMonth($user, $normalizedMonth);

        if ($limit === null) {
            $limit = new UserMonthlyLimit();
            $limit->setUser($user);
            $limit->setUsageMonth($normalizedMonth);
            $limit->setUploadCount(0);
        }

        return $limit;
    }

    /**
     * Find a monthly limit for a specific user and month.
     *
     * @throws Exception
     */
    public function findOneByUserAndMonth(User $user, DateTimeImmutable $month): ?UserMonthlyLimit
    {
        // Normalize to the first day of the month
        $normalizedMonth = new DateTimeImmutable($month->format('Y-m-01'));

        return $this->createQueryBuilder('uml')
            ->andWhere('uml.user = :user')
            ->andWhere('uml.usageMonth = :month')
            ->setParameter('user', $user)
            ->setParameter('month', $normalizedMonth, Types::DATE_IMMUTABLE)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all monthly limits for a specific user.
     *
     * @return UserMonthlyLimit[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('uml')
            ->andWhere('uml.user = :user')
            ->setParameter('user', $user)
            ->orderBy('uml.usageMonth', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the current month's upload count for a user.
     *
     * @throws Exception
     */
    public function getCurrentMonthUploadCount(User $user): int
    {
        $currentMonth = new DateTimeImmutable('first day of this month');

        $limit = $this->findOneByUserAndMonth($user, $currentMonth);

        return $limit ? $limit->getUploadCount() : 0;
    }

    /**
     * Increment upload count for a user in a specific month.
     *
     * @throws Exception
     */
    public function incrementUploadCount(User $user, int $increment = 1): UserMonthlyLimit
    {
        $currentMonth = new DateTimeImmutable('first day of this month');

        $limit = $this->findOrCreate($user, $currentMonth);
        $limit->setUploadCount($limit->getUploadCount() + $increment);

        $this->save($limit, true);

        return $limit;
    }

    /**
     * Save a monthly limit entity.
     */
    public function save(UserMonthlyLimit $monthlyLimit, bool $flush = false): void
    {
        $this->getEntityManager()->persist($monthlyLimit);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a monthly limit entity.
     */
    public function remove(UserMonthlyLimit $monthlyLimit, bool $flush = false): void
    {
        $this->getEntityManager()->remove($monthlyLimit);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
