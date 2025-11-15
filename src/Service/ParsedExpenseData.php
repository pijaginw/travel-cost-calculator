<?php

namespace App\Service;

use App\Entity\ExpenseCategory;
use InvalidArgumentException;

/**
 * Data Transfer Object for the structured data extracted from a receipt image.
 */
class ParsedExpenseData
{
    /**
     * @param float  $amount   the total monetary amount extracted from the receipt
     * @param string $category the suggested expense category must be one of the predefined categories
     *
     * @throws InvalidArgumentException if the category is invalid
     */
    public function __construct(
        private float $amount,
        private string $category,
    ) {
        if (!in_array(ExpenseCategory::from($this->category), ExpenseCategory::cases(), true)) {
            throw new InvalidArgumentException('Invalid category provided.');
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCategory(): string
    {
        return $this->category;
    }
}
