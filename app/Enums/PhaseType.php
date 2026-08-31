<?php

namespace App\Enums;

enum PhaseType: int
{
    case Standard = 0;
    case Amendment = 1;

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard Phase',
            self::Amendment => 'Amendment Phase',
        };
    }
}
