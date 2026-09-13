<?php

namespace App\Http\Requests\Hr\Concerns;

use App\Models\StaffPosition;
use Closure;

/**
 * Refuses a member placed in a position that has no KPI.
 *
 * The dropdown already leaves those positions out, so this is the rule behind
 * it - a posted position_id is user input. The Administrator position is exempt;
 * see StaffPosition::acceptsMembers().
 */
trait ChecksPositionAcceptsMembers
{
    /**
     * @param  int|null  $currentPositionId  a member may stay where they already
     *                                        are, even if that position has since
     *                                        lost its KPI
     */
    protected function positionAcceptsMembers(?int $currentPositionId = null): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($currentPositionId) {
            if ($currentPositionId !== null && (int) $value === $currentPositionId) {
                return;
            }

            $position = StaffPosition::find($value);

            if ($position && ! $position->acceptsMembers()) {
                $fail('"'.$position->position_name.'" has no KPI items with marks yet. Add at least one before assigning members to it.');
            }
        };
    }
}
