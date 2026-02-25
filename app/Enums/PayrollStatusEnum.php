<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum PayrollStatusEnum
 *
 * Represents the restricted set of statuses available for a payroll record.
 */
enum PayrollStatusEnum: string
{
    case DRAFT = 'draft';
    case PAID = 'paid';
}
