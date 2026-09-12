<?php

namespace App\Enums;

/**
 * Where a task sits in the pipeline.
 *
 * Replaces PhaseProgressStatus, which only knew Progress / OnHold / Complete -
 * no way to say a task hadn't been picked up yet, or was waiting on review.
 *
 * isDone() is the single place that decides what counts as finished: the
 * delivery score, the completed_at stamp and the progress bar all ask this
 * rather than comparing against a case by name.
 */
enum TaskStatus: int
{
    case ToDo = 1;
    case InProgress = 2;
    case Review = 3;
    case Blocked = 4;
    case Done = 5;

    public function label(): string
    {
        return match ($this) {
            self::ToDo => 'To Do',
            self::InProgress => 'In Progress',
            self::Review => 'Review',
            self::Blocked => 'Blocked',
            self::Done => 'Done',
        };
    }

    /**
     * Finished work - the only state that earns its tag's points.
     */
    public function isDone(): bool
    {
        return $this === self::Done;
    }

    /**
     * Theme status-pill colour id: 1 green, 2 red, 3 orange, 4 blue, 5 pink.
     */
    public function colourId(): int
    {
        return match ($this) {
            self::ToDo => 5,
            self::InProgress => 4,
            self::Review => 3,
            self::Blocked => 2,
            self::Done => 1,
        };
    }
}
