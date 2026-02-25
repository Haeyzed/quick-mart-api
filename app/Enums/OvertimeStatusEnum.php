<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum OvertimeStatusEnum
 *
 * Represents the restricted set of statuses available for an overtime request.
 */
enum OvertimeStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
