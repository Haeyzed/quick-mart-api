<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum LeaveStatusEnum
 *
 * Represents the restricted set of statuses available for an employee's leave request.
 */
enum LeaveStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
