<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Reads config/branding.php and resolves it into the values the views and
 * stylesheets need: asset URLs, the colour token map, and the login
 * background rules.
 *
 * Every lookup goes through get(), which is the single seam a future
 * database-backed settings screen would hook into - see the "Configuration
 * dashboard" note in README.md.
 */
class Branding
{
    /**
     * Renders declarations for one CSS rule, e.g. "color: #fff; width: 2px;".
     *
     * Values come from config rather than user input, but they are emitted
     * unescaped into a <style> block, so anything that could close the block
     * or start a new rule is stripped rather than trusted.
     *
     * @param  array<string, string>  $declarations
     */
    public static function cssDeclarations(array $declarations): string
    {
        return collect($declarations)
            ->map(fn ($value, $property) => static::cssSafe($property).': '.static::cssSafe($value).';')
            ->implode("\n        ");
    }

    private static function cssSafe(string $value): string
    {
        return trim(str_replace(['<', '>', '{', '}', ';', '@'], '', $value));
    }

    /**
     * A branding value by dot key, e.g. get('fonts.body').
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return config("branding.{$key}", $default);
    }

    public static function name(): string
    {
        return (string) static::get('name', config('app.name'));
    }

    public static function company(): string
    {
        return (string) (static::get('company') ?: static::name());
    }

    /**
     * The copyright line, defaulting to "© {year} {company}. All Rights
     * Reserved." so a project only overrides it when it needs different
     * wording.
     */
    public static function copyright(): string
    {
        $custom = trim((string) static::get('copyright'));

        if ($custom !== '') {
            return $custom;
        }

        return '© Copyright '.date('Y').' '.static::company().'. All Rights Reserved.';
    }

    /**
     * Resolves a logo slot to a URL, walking the fallback chain so a project
     * that sets only APP_LOGO still gets every slot filled.
     *
     * $slot: main | light | dark | small | login | sidebar | navbar | mobile
     */
    public static function logo(string $slot = 'main'): string
    {
        $chain = match ($slot) {
            'light' => ['logo_light', 'logo'],
            'dark' => ['logo_dark', 'logo'],
            'small' => ['logo_small', 'logo'],
            'login' => ['logo_login', 'logo_light', 'logo'],
            'sidebar' => ['logo_sidebar', 'logo_light', 'logo'],
            'navbar' => ['logo_navbar', 'logo_light', 'logo'],
            'mobile' => ['logo_mobile', 'logo_small', 'logo'],
            default => ['logo'],
        };

        foreach ($chain as $key) {
            $path = static::get($key);

            if (filled($path)) {
                return static::url($path);
            }
        }

        return '';
    }

    public static function favicon(): string
    {
        return static::url(static::get('favicon'));
    }

    public static function appleTouchIcon(): string
    {
        return static::url(static::get('apple_touch_icon'));
    }

    public static function ogImage(): string
    {
        return static::url(static::get('og_image'));
    }

    /**
     * Turns a configured path into a URL. Absolute URLs and data URIs are
     * passed through untouched so branding assets can live on a CDN.
     */
    public static function url(?string $path): string
    {
        if (blank($path)) {
            return '';
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    /**
     * The Google Fonts stylesheet URL, or null when none is configured, so
     * the layout can skip the <link> entirely rather than requesting an
     * empty family.
     */
    public static function googleFontsUrl(): ?string
    {
        $families = trim((string) static::get('fonts.google_fonts'));

        if ($families === '') {
            return null;
        }

        $query = collect(explode('|', $families))
            ->map(fn ($family) => 'family='.str_replace(' ', '+', trim($family)))
            ->implode('&');

        return "https://fonts.googleapis.com/css2?{$query}&display=swap";
    }

    /**
     * The resolved colour map: explicit config values win, then the active
     * preset, then the default preset. Keys with no value anywhere are
     * dropped so the stylesheet fallback applies instead.
     *
     * @return array<string, string>
     */
    public static function colors(): array
    {
        $presets = (array) static::get('presets', []);
        $active = (array) ($presets[static::get('theme', 'default')] ?? []);
        $default = (array) ($presets['default'] ?? []);

        $resolved = array_merge($default, $active);

        foreach ((array) static::get('colors', []) as $token => $value) {
            if (filled($value)) {
                $resolved[$token] = $value;
            }
        }

        return array_filter($resolved, fn ($value) => filled($value));
    }

    /**
     * The colour map plus typography, as the --brand-* custom properties
     * emitted on :root.
     *
     * @return array<string, string>
     */
    public static function cssVariables(): array
    {
        $vars = [];

        foreach (static::colors() as $token => $value) {
            $vars["--brand-{$token}"] = $value;
        }

        $vars['--brand-font-body'] = (string) static::get('fonts.body');
        $vars['--brand-font-heading'] = (string) (static::get('fonts.heading') ?: static::get('fonts.body'));
        $vars['--brand-font-size'] = (string) static::get('fonts.base_size');
        $vars['--brand-font-weight-normal'] = (string) static::get('fonts.weight_normal');
        $vars['--brand-font-weight-bold'] = (string) static::get('fonts.weight_bold');

        return array_filter($vars, fn ($value) => filled($value));
    }

    /**
     * CSS declarations for the login background. Falls back to a flat colour
     * when no image is configured, and layers the overlay over the image
     * when one is set.
     *
     * @return array<string, string>
     */
    public static function backgroundStyles(): array
    {
        $background = (array) static::get('background', []);
        $image = trim((string) ($background['image'] ?? ''));
        $overlay = trim((string) ($background['overlay'] ?? ''));
        $colour = $background['colour'] ?? '';

        if ($image === '') {
            return array_filter(['background-color' => $colour]);
        }

        $layers = [];

        if ($overlay !== '' && $overlay !== 'rgba(0, 0, 0, 0)') {
            $layers[] = "linear-gradient({$overlay}, {$overlay})";
        }

        $layers[] = 'url("'.static::url($image).'")';

        return array_filter([
            'background-color' => $colour,
            'background-image' => implode(', ', $layers),
            'background-size' => $background['size'] ?? null,
            'background-position' => $background['position'] ?? null,
            'background-repeat' => $background['repeat'] ?? null,
            'background-attachment' => $background['attachment'] ?? null,
        ]);
    }
}
