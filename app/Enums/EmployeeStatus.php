<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case TERMINATED = 'terminated';

    public static function fromProvider1(string $status): self
    {
        return match ($status) {
            'active' => self::ACTIVE,
            'inactive' => self::INACTIVE,
            'terminated' => self::TERMINATED,
            default => self::ACTIVE,
        };
    }

    public static function fromProvider2(string $status): self
    {
        return match ($status) {
            'employed' => self::ACTIVE,
            'on_leave' => self::INACTIVE,
            'terminated' => self::TERMINATED,
            default => self::ACTIVE,
        };
    }
}
