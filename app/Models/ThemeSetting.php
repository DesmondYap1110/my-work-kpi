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
        'mobile-menu' => 'Mobile menu - bottom bar on phones',
        'accent' => 'Accent - active menu glow',
        'background' => 'Page background',
        'text' => 'Text',
    ];

    protected $table = 'theme_setting';

    /** Built-in login backgrounds, under public/. */
    public const LOGIN_IMAGES = [
        // Green abstract backgrounds made for the green themes (vector, any size).
        'assets/img/bg/green-waves.svg',
        'assets/img/bg/green-mesh.svg',
        'assets/img/bg/green-leaves.svg',
        'assets/img/bg/bg-3.jpg',
        'assets/img/bg/bg.jpg',
        'assets/img/bg/bg-1.jpg',
        'assets/img/bg/bg-2.jpg',
    ];

    /** Where uploaded login backgrounds go, on the public disk. */
    public const LOGIN_UPLOAD_DIR = 'login-backgrounds';

    protected $fillable = ['preset', 'colors', 'login_background_image', 'login_background_color', 'login_overlay'];

    protected $casts = ['colors' => 'array', 'login_overlay' => 'integer'];

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
     * @return array{preset: string|null, colors: array<string, string>, login_image: string|null, login_color: string|null, login_overlay: int|null}
     */
    public static function cached(): array
    {
        $empty = ['preset' => null, 'colors' => [], 'login_image' => null, 'login_color' => null, 'login_overlay' => null];

        try {
            return Cache::rememberForever(self::CACHE_KEY, function () use ($empty) {
                $row = static::find(1);

                return $row ? [
                    'preset' => $row->preset,
                    'colors' => (array) ($row->colors ?? []),
                    'login_image' => $row->login_background_image,
                    'login_color' => $row->login_background_color,
                    'login_overlay' => $row->login_overlay,
                ] : $empty;
            }) + $empty;
        } catch (Throwable) {
            // No table yet: the query throws, so nothing is cached and the
            // next request tries again once migrations have run.
            return $empty;
        }
    }

    /**
     * Whether a stored login image is one uploaded here (and so ours to delete).
     */
    public static function isUploadedLoginImage(?string $path): bool
    {
        return is_string($path) && str_starts_with($path, 'storage/'.self::LOGIN_UPLOAD_DIR.'/');
    }
}
