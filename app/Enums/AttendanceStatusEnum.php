<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum AttendanceStatusEnum
 *
 * Represents the restricted set of statuses available for a daily attendance record.
 */
enum AttendanceStatusEnum: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case ABSENT = 'absent';
}
