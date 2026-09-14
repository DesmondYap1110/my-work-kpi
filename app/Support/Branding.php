<?php

namespace App\Support;

use App\Models\ThemeSetting;
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
     * The resolved colour map, each layer winning over the one before:
     *
     *   default preset < chosen preset < colours changed in Settings > Theme Setting
     *
     * Colours are set only in the app - there is no .env override. The chosen
     * preset is the one picked in Theme Setting, else config 'theme'. Keys with
     * no value anywhere are dropped so the stylesheet fallback applies instead.
     *
     * @return array<string, string>
     */
    public static function colors(): array
    {
        $saved = ThemeSetting::cached();

        return static::resolveColors(
            (array) static::get('presets', []),
            $saved['preset'] ?: (string) static::get('theme', 'default'),
            static::expandCustomColors($saved['colors']),
        );
    }

    /**
     * The layering itself, pure so it can be tested without config or a
     * database.
     *
     * @param  array<string, array<string, string>>  $presets
     * @param  array<string, string>  $custom
     * @return array<string, string>
     */
    public static function resolveColors(array $presets, string $theme, array $custom): array
    {
        $resolved = array_merge((array) ($presets['default'] ?? []), (array) ($presets[$theme] ?? []));

        foreach ($custom as $token => $value) {
            if (filled($value)) {
                $resolved[$token] = $value;
            }
        }

        // The phone bottom bar matches the sidebar unless it is given its own.
        if (blank($resolved['mobile-menu'] ?? null) && filled($resolved['sidebar'] ?? null)) {
            $resolved['mobile-menu'] = $resolved['sidebar'];
        }

        return array_filter($resolved, fn ($value) => filled($value));
    }

    /**
     * An admin picks a handful of colours; the tokens that should move with
     * them follow, so a new primary also recolours buttons, links, focus
     * rings and the darker hover shades. Only valid #RRGGBB values pass.
     *
     * @param  array<string, mixed>  $custom
     * @return array<string, string>
     */
    public static function expandCustomColors(array $custom): array
    {
        $custom = array_filter($custom, fn ($value) => is_string($value) && static::isHex($value));
        $out = $custom;

        if (isset($custom['primary'])) {
            $out += [
                'primary-hover' => static::darken($custom['primary'], 15),
                'button' => $custom['primary'],
                'button-hover' => static::darken($custom['primary'], 15),
                'input-focus' => $custom['primary'],
                'link' => $custom['primary'],
            ];
        }

        if (isset($custom['secondary'])) {
            $out += ['secondary-hover' => static::darken($custom['secondary'], 15)];
        }

        if (isset($custom['text'])) {
            $out += ['input-text' => $custom['text']];
        }

        return $out;
    }

    public static function isHex(string $value): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $value);
    }

    /**
     * #RRGGBB moved $percent of the way towards black.
     */
    public static function darken(string $hex, int $percent): string
    {
        $factor = 1 - max(0, min(100, $percent)) / 100;

        return '#'.collect(str_split(ltrim($hex, '#'), 2))
            ->map(fn ($pair) => str_pad(dechex((int) round(hexdec($pair) * $factor)), 2, '0', STR_PAD_LEFT))
            ->implode('');
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
        $background = static::loginBackground();

        return static::backgroundStylesFrom($background);
    }

    /**
     * The login background settings: config/branding.php, with anything saved
     * under Settings > Theme Setting on top. Overlay comes back as a CSS colour.
     *
     * @return array<string, mixed>
     */
    public static function loginBackground(): array
    {
        $background = (array) static::get('background', []);
        $saved = ThemeSetting::cached();

        if (filled($saved['login_image'])) {
            $background['image'] = $saved['login_image'] === 'none' ? '' : $saved['login_image'];
        }

        if (filled($saved['login_color']) && static::isHex($saved['login_color'])) {
            $background['colour'] = $saved['login_color'];
        }

        if ($saved['login_overlay'] !== null) {
            $background['overlay'] = 'rgba(0, 0, 0, '.round(max(0, min(80, (int) $saved['login_overlay'])) / 100, 2).')';
        }

        return $background;
    }

    /**
     * @param  array<string, mixed>  $background
     * @return array<string, string>
     */
    public static function backgroundStylesFrom(array $background): array
    {
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
