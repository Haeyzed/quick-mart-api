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
    case Active = 'active';
    case Suspended = 'suspended';
    case Resigned = 'resigned';
    case Terminated = 'terminated';
}
