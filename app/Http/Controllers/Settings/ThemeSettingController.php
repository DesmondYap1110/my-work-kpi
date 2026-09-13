<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\ThemeSetting;
use App\Support\Branding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Settings > Theme Setting: the app's colours, chosen in the app instead of
 * .env. Pick a preset, optionally change a few of its colours, save.
 *
 * What is saved layers over config/branding.php - see Branding::colors().
 */
class ThemeSettingController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Settings', 'route' => '', 'active' => false],
            ['name' => 'Theme Setting', 'route' => '', 'active' => true],
        ];
    }

    public function edit(): View
    {
        $setting = ThemeSetting::current();
        $presets = (array) Branding::get('presets', []);
        $activePreset = $setting->preset ?: (string) Branding::get('theme', 'default');

        return view('settings.theme', [
            'presets' => collect($presets)->map(fn ($colors, $key) => [
                'key' => $key,
                'label' => $key === 'default' ? 'Ocean Blue' : Str::headline($key),
                // The resolved map for this preset alone, for its swatches and
                // for the live preview when it is picked.
                'colors' => Branding::resolveColors($presets, $key, [], []),
            ])->values(),
            'activePreset' => $activePreset,
            'custom' => (array) ($setting->colors ?? []),
            'current' => Branding::colors(),
            'editable' => ThemeSetting::EDITABLE,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $presets = array_keys((array) Branding::get('presets', []));

        $validated = $request->validate([
            'preset' => ['required', Rule::in($presets)],
            'colors' => ['array'],
            'colors.*' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'colors.*.regex' => 'Each colour must be a hex value like #22A65E.',
        ]);

        // Keep only the editable colours that differ from the chosen preset -
        // a colour left at the preset's own value is not a customisation, and
        // storing it would stop it following the preset if the preset changes.
        $base = Branding::resolveColors((array) Branding::get('presets', []), $validated['preset'], [], []);
        $colors = collect($validated['colors'] ?? [])
            ->only(array_keys(ThemeSetting::EDITABLE))
            ->filter(fn ($value, $token) => filled($value) && strcasecmp($value, $base[$token] ?? '') !== 0)
            ->map(fn ($value) => strtoupper($value))
            ->all();

        ThemeSetting::current()->update([
            'preset' => $validated['preset'],
            'colors' => $colors ?: null,
        ]);

        return back()->with('status', 'Theme saved. Every page now uses the new colours.');
    }

    /**
     * Back to what .env says: no preset chosen here, no custom colours.
     */
    public function reset(): RedirectResponse
    {
        ThemeSetting::current()->update(['preset' => null, 'colors' => null]);

        return back()->with('status', 'Theme reset to the default from the app configuration.');
    }
}
