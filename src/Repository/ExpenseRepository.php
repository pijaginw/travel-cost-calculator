<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\ExpenseCategory;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    /**
     * Find all expenses for a specific trip
     *
     * @param Trip $trip
     * @return Expense[]
     */
    public function findByTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.trip = :trip')
            ->setParameter('trip', $trip)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find expenses for a trip filtered by category
     *
     * @param Trip $trip
     * @param ExpenseCategory $category
     * @return Expense[]
     */
    public function findByTripAndCategory(Trip $trip, ExpenseCategory $category): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.trip = :trip')
            ->andWhere('e.category = :category')
            ->setParameter('trip', $trip)
            ->setParameter('category', $category)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate total expenses for a trip
     *
     * @param Trip $trip
     * @return string
     */
    public function getTotalAmountForTrip(Trip $trip): string
    {
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.amount) as total')
            ->andWhere('e.trip = :trip')
            ->setParameter('trip', $trip)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? '0.00';
    }

    /**
     * Calculate total expenses for a trip by category
     *
     * @param Trip $trip
     * @param ExpenseCategory $category
     * @return string
     */
    public function getTotalAmountForTripByCategory(Trip $trip, ExpenseCategory $category): string
    {
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.amount) as total')
            ->andWhere('e.trip = :trip')
            ->andWhere('e.category = :category')
            ->setParameter('trip', $trip)
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? '0.00';
    }

    /**
     * Get expense count for a trip
     *
     * @param Trip $trip
     * @return int
     */
    public function countByTrip(Trip $trip): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.trip = :trip')
            ->setParameter('trip', $trip)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save an expense entity
     *
     * @param Expense $expense
     * @param bool $flush
     * @return void
     */
    public function save(Expense $expense, bool $flush = false): void
    {
        $this->getEntityManager()->persist($expense);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove an expense entity
     *
     * @param Expense $expense
     * @param bool $flush
     * @return void
     */
    public function remove(Expense $expense, bool $flush = false): void
    {
        $this->getEntityManager()->remove($expense);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
