<?php

namespace App\Ai\Tools;

use App\Models\Staff;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;

/**
 * Base for the KPI Assistant's lookups.
 *
 * Every tool is read-only, and who may see what is enforced here in PHP - not
 * left to the model's instructions, which a question could talk it out of:
 *
 *   administrator  any member, team or report
 *   member         only themselves - naming anyone else returns "not allowed"
 */
abstract class AssistantTool implements Tool
{
    public function __construct(protected readonly Staff $user)
    {
    }

    /**
     * The name the model calls it by - member_kpi_score, app_guide... Plain
     * snake_case reads more reliably to a small local model than class names.
     */
    public function name(): string
    {
        return Str::snake(class_basename($this));
    }

    protected function isAdmin(): bool
    {
        return $this->user->isAdmin();
    }

    /**
     * The member a question is about: blank or "me" is the person asking; a
     * name (or part of one) is looked up, but only an administrator may ask
     * about someone else.
     *
     * @return Staff|string  the member, or a message to hand back to the model
     */
    protected function resolveMember(?string $name): Staff|string
    {
        $name = trim((string) $name);

        if ($name === '' || in_array(strtolower($name), ['me', 'myself', 'my', 'i', 'self'], true)) {
            if ($this->isAdmin()) {
                return 'The administrator has no KPI of their own. Ask about a member by name.';
            }

            return $this->user;
        }

        $matches = Staff::query()->excludingAdmin()
            ->with(['position', 'team'])
            ->where('staff_name', 'like', '%'.$name.'%')
            ->orderBy('staff_name')
            ->limit(5)
            ->get();

        if (! $this->isAdmin()) {
            // A member may only ever get themselves back.
            return $matches->contains('id', $this->user->id) && $matches->count() === 1
                ? $this->user
                : 'Not allowed: members can only look up their own information.';
        }

        return match ($matches->count()) {
            0 => "No member found matching \"{$name}\".",
            1 => $matches->first(),
            default => 'Several members match "'.$name.'": '.$matches->pluck('staff_name')->implode(', ').'. Ask again with the full name.',
        };
    }

    /**
     * A named period into dates - the same presets as KPI Report.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    protected function period(?string $period, ?string $from = null, ?string $to = null): array
    {
        $now = now();

        if ($period === 'custom' && $from && $to) {
            try {
                $start = Carbon::parse($from)->startOfDay();
                $end = Carbon::parse($to)->endOfDay();

                return $start->lte($end)
                    ? [$start, $end, $start->format('d M Y').' - '.$end->format('d M Y')]
                    : [$end->copy()->startOfDay(), $start->copy()->endOfDay(), $end->format('d M Y').' - '.$start->format('d M Y')];
            } catch (\Throwable) {
                // Fall through to this year.
            }
        }

        return match ($period) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'this month ('.$now->format('M Y').')'],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth(), 'last month ('.$now->copy()->subMonthNoOverflow()->format('M Y').')'],
            'this_quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter(), 'this quarter'],
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear(), 'last year ('.$now->copy()->subYear()->year.')'],
            default => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'this year ('.$now->year.')'],
        };
    }

    /** Periods every period-taking tool accepts. */
    protected const PERIODS = ['this_year', 'this_month', 'last_month', 'this_quarter', 'last_year', 'custom'];

    protected function limit(): int
    {
        return (int) config('assistant.row_limit', 15);
    }

    protected function num(mixed $value): ?float
    {
        return $value === null ? null : round((float) $value, 2);
    }

    protected function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
