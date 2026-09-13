<?php

namespace App\Enums;

/**
 * Where an appraisal is in its life.
 *
 * The distinction that matters is Generated: until the appraiser generates it,
 * the member cannot see it at all. A half-filled form is a private working
 * note, not feedback, and nobody should read their own review while the person
 * writing it is still deciding.
 *
 * Stored as a string rather than an int - unlike the other enums here, these
 * values are read straight out of the database by a person checking on a
 * review, and "draft" says more than 1.
 */
enum AssessmentStatus: string
{
    case Draft = 'draft';
    case Generated = 'generated';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Generated => 'Generated',
        };
    }

    /**
     * Theme status-pill colour id: 1 green, 2 red, 3 orange, 4 blue, 5 pink.
     */
    public function colourId(): int
    {
        return match ($this) {
            self::Draft => 3,
            self::Generated => 1,
        };
    }

    public function isVisibleToStaff(): bool
    {
        return $this === self::Generated;
    }
}
