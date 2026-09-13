<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * The colour theme chosen under Settings > Theme Setting. One row, id 1.
 *
 * Read on every page (the colours are emitted into the layout), so it is
 * cached and the cache is dropped whenever the row is saved. See
 * App\Support\Branding::colors() for how it layers over config/branding.php.
 */
class ThemeSetting extends Model
{
    public const CACHE_KEY = 'theme_setting';

    /**
     * The colours an admin may change. Each one's related tokens follow it -
     * see App\Support\Branding::expandCustomColors().
     */
    public const EDITABLE = [
        'primary' => 'Primary - buttons, links, titles',
        'secondary' => 'Secondary - table headers, dashboard tiles',
        'sidebar' => 'Sidebar',
        'accent' => 'Accent - active menu glow',
        'background' => 'Page background',
        'text' => 'Text',
    ];

    protected $table = 'theme_setting';

    protected $fillable = ['preset', 'colors'];

    protected $casts = ['colors' => 'array'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    /**
     * The saved choice as plain values, or nulls when there is none - including
     * before the table exists (a fresh install mid-migration, or a console
     * command), so the layout never fails on its colours.
     *
     * @return array{preset: string|null, colors: array<string, string>}
     */
    public static function cached(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                $row = static::find(1);

                return ['preset' => $row?->preset, 'colors' => (array) ($row?->colors ?? [])];
            });
        } catch (Throwable) {
            // No table yet: the query throws, so nothing is cached and the
            // next request tries again once migrations have run.
            return ['preset' => null, 'colors' => []];
        }
    }
}
