<?php

namespace App\Enums;

enum PhaseApprovalStatus: int
{
    case NoSubmission = 0;
    case Pending = 1;
    case Approved = 2;
    case Rejected = 3;

    public function label(): string
    {
        return match ($this) {
            self::NoSubmission => 'Not Submitted',
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
