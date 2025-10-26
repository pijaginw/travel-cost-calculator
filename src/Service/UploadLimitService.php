<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service to enforce the monthly receipt upload limit.
 */
class UploadLimitService
{
    private const MONTHLY_LIMIT = 100; // FR-023: A usage limit of 100 receipt uploads

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * US-012: Checks if the user is below the monthly limit.
     */
    public function canUserUploadReceipt(User $user): bool
    {
        // --- SIMULATED LOGIC START ---

        // In a real application, this would query the database for successful uploads
        // by the user in the current calendar month.

        // Example DQL (not runnable here, just conceptual):
        /*
        $count = $this->entityManager->createQuery(
            'SELECT COUNT(e.id) FROM App\Entity\Expense e
             JOIN e.trip t
             WHERE t.user = :user AND e.createdAt >= :firstDayOfMonth'
        )
        ->setParameter('user', $user)
        ->setParameter('firstDayOfMonth', new \DateTime('first day of this month'))
        ->getSingleScalarResult();
        */

        $currentUploads = 5; // Placeholder for a real count

        // --- SIMULATED LOG END ---

        return $currentUploads < self::MONTHLY_LIMIT;
    }

    /**
     * Increments the user's upload count upon successful save (US-007 acceptance).
     */
    public function incrementUploadCount(User $user): void
    {
        // Logic to increment the persistent upload counter for the month.
        // This is crucial for enforcing the limit in subsequent checks.
    }
}
