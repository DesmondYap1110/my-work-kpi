<?php

namespace App\Enums;

/**
 * How urgent a task is. Nullable on the task itself - most work has no
 * particular urgency, and forcing a choice would make the field noise.
 */
enum TaskPriority: int
{
    case Low = 1;
    case Normal = 2;
    case High = 3;
    case Urgent = 4;

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Normal => 'Normal',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    /**
     * Theme status-pill colour id: 1 green, 2 red, 3 orange, 4 blue, 5 pink.
     */
    public function colourId(): int
    {
        return match ($this) {
            self::Low => 5,
            self::Normal => 4,
            self::High => 3,
            self::Urgent => 2,
        };
    }
}
