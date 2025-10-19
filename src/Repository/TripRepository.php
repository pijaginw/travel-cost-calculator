<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trip>
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    /**
     * Find all trips for a specific user
     *
     * @param User $user
     * @return Trip[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a trip by name for a specific user
     *
     * @param User $user
     * @param string $tripName
     * @return Trip|null
     */
    public function findOneByUserAndName(User $user, string $tripName): ?Trip
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.tripName = :tripName')
            ->setParameter('user', $user)
            ->setParameter('tripName', $tripName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Save a trip entity
     *
     * @param Trip $trip
     * @param bool $flush
     * @return void
     */
    public function save(Trip $trip, bool $flush = false): void
    {
        $this->getEntityManager()->persist($trip);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a trip entity
     *
     * @param Trip $trip
     * @param bool $flush
     * @return void
     */
    public function remove(Trip $trip, bool $flush = false): void
    {
        $this->getEntityManager()->remove($trip);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
