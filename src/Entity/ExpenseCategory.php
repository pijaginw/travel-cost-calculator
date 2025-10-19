<?php

namespace App\Entity;

/**
 * Maps directly to the PostgreSQL 'expense_category' ENUM type.
 */
enum ExpenseCategory: string
{
    case Transportation = 'Transportation';
    case Accomodation = 'Accomodation';
    case FoodAndDrink = 'Food & Drink';
    case Activities = 'Activities';
    case Uncategorized = 'Uncategorized';
}
