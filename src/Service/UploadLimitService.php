<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserMonthlyLimitRepository;
use Exception;

/**
 * Service to enforce the monthly receipt upload limit.
 */
class UploadLimitService
{
    private const MONTHLY_LIMIT = 100; // FR-023: A usage limit of 100 receipt uploads

    public function __construct(private readonly UserMonthlyLimitRepository $userMonthlyLimitRepository)
    {
    }

    /**
     * US-012: Checks if the user is below the monthly limit.
     *
     * @throws Exception
     */
    public function canUserUploadReceipt(User $user): bool
    {
        $limit = $this->userMonthlyLimitRepository->getCurrentMonthUploadCount($user);

        return $limit < self::MONTHLY_LIMIT;
    }

    /**
     * Increments the user's upload count upon successful save (US-007 acceptance).
     *
     * @throws Exception
     */
    public function incrementUploadCount(User $user): void
    {
        $this->userMonthlyLimitRepository->incrementUploadCount($user);
    }
}
