<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Custom Doctrine type for PostgreSQL enum "expense_category".
 *
 * Makes Doctrine understand that this type exists in the DB
 * and how to handle it when persisting/fetching.
 */
final class ExpenseCategoryType extends Type
{
    public const NAME = 'expense_category';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        // Keep the exact PostgreSQL enum type name
        return self::NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        // Doctrine will give you raw DB value as string
        // (which matches your PHP enum cases)
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        // You could add validation here (ensure it's a valid enum case)
        return $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        // Prevents Doctrine from mistaking this for a built-in string type
        return true;
    }
}
