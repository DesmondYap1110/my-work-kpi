<?php

namespace App\Enums;

enum ObjectiveType: int
{
    case Standard = 0;
    case Extra = 1;

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Extra => 'Extra (Bonus)',
        };
    }
}
