<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum EmploymentStatusEnum
 *
 * Represents the lifecycle status of an employee within the organization.
 */
enum EmploymentStatusEnum: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case RESIGNED = 'resigned';
    case TERMINATED = 'terminated';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
