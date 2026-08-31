<?php

namespace App\Enums;

enum PhaseProgressStatus: int
{
    case Progress = 1;
    case OnHold = 2;
    case Complete = 3;

    public function label(): string
    {
        return match ($this) {
            self::Progress => 'In-Progress',
            self::OnHold => 'On Hold',
            self::Complete => 'Completed',
        };
    }
}
