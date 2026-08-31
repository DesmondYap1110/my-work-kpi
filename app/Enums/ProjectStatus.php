<?php

namespace App\Enums;

enum ProjectStatus: int
{
    case Active = 1;
    case Completed = 2;
    case InProgress = 3;
    case Cancelled = 4;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::InProgress => 'In-Progress',
            self::Cancelled => 'Cancelled',
        };
    }
}
