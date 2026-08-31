<?php

namespace App\Enums;

enum ProjectKpiStatus: int
{
    case Approved = 1;
    case Rejected = 2;

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
