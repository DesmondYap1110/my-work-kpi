<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\ThemeSetting;
use App\Support\Branding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                'colors' => Branding::resolveColors($presets, $key, []),
            ])->values(),
            'activePreset' => $activePreset,
            'custom' => (array) ($setting->colors ?? []),
            'current' => Branding::colors(),
            'editable' => ThemeSetting::EDITABLE,
            'login' => Branding::loginBackground(),
            'loginImages' => ThemeSetting::LOGIN_IMAGES,
            'loginUploaded' => ThemeSetting::isUploadedLoginImage($setting->login_background_image) ? $setting->login_background_image : null,
            'loginOverlay' => $setting->login_overlay ?? $this->overlayPercent((string) (Branding::get('background.overlay') ?? '')),
        ]);
    }

    /** "rgba(0, 0, 0, 0.35)" -> 35; anything else -> 0. */
    private function overlayPercent(string $css): int
    {
        return preg_match('/rgba\([^,]+,[^,]+,[^,]+,\s*([\d.]+)\)/', $css, $m) ? (int) round((float) $m[1] * 100) : 0;
    }

    public function update(Request $request): RedirectResponse
    {
        $presets = array_keys((array) Branding::get('presets', []));

        $setting = ThemeSetting::current();

        $validated = $request->validate([
            'preset' => ['required', Rule::in($presets)],
            'colors' => ['array'],
            'colors.*' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            // Login page: a built-in image, the current upload, a new upload, or none.
            'login_image' => ['required', Rule::in(array_merge(ThemeSetting::LOGIN_IMAGES, ['none', 'uploaded', 'upload']))],
            'login_upload' => ['nullable', 'required_if:login_image,upload', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=800,min_height=450'],
            'login_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'login_overlay' => ['required', 'integer', 'min:0', 'max:80'],
        ], [
            'colors.*.regex' => 'Each colour must be a hex value like #22A65E.',
            'login_color.regex' => 'The login colour must be a hex value like #0B0A1F.',
            'login_upload.required_if' => 'Choose an image file to upload.',
            'login_upload.dimensions' => 'The image should be at least 800 x 450 pixels so it stays sharp on a large screen.',
        ]);

        $loginImage = match ($validated['login_image']) {
            'uploaded' => ThemeSetting::isUploadedLoginImage($setting->login_background_image) ? $setting->login_background_image : null,
            'upload' => 'storage/'.$request->file('login_upload')->store(ThemeSetting::LOGIN_UPLOAD_DIR, 'public'),
            default => $validated['login_image'],
        };

        // A replaced upload is no longer used anywhere.
        if (ThemeSetting::isUploadedLoginImage($setting->login_background_image) && $setting->login_background_image !== $loginImage) {
            $this->deleteUpload($setting->login_background_image);
        }

        // Keep only the editable colours that differ from the chosen preset -
        // a colour left at the preset's own value is not a customisation, and
        // storing it would stop it following the preset if the preset changes.
        $base = Branding::resolveColors((array) Branding::get('presets', []), $validated['preset'], []);
        $colors = collect($validated['colors'] ?? [])
            ->only(array_keys(ThemeSetting::EDITABLE))
            ->filter(fn ($value, $token) => filled($value) && strcasecmp($value, $base[$token] ?? '') !== 0)
            ->map(fn ($value) => strtoupper($value))
            ->all();

        $setting->update([
            'preset' => $validated['preset'],
            'colors' => $colors ?: null,
            'login_background_image' => $loginImage,
            'login_background_color' => strtoupper($validated['login_color']),
            'login_overlay' => (int) $validated['login_overlay'],
        ]);

        return back()->with('status', 'Theme saved. Every page, and the login page, now uses it.');
    }

    /**
     * Back to what .env says: no preset chosen here, no custom colours, and
     * the login background from the app configuration.
     */
    public function reset(): RedirectResponse
    {
        $setting = ThemeSetting::current();

        if (ThemeSetting::isUploadedLoginImage($setting->login_background_image)) {
            $this->deleteUpload($setting->login_background_image);
        }

        $setting->update([
            'preset' => null, 'colors' => null,
            'login_background_image' => null, 'login_background_color' => null, 'login_overlay' => null,
        ]);

        return back()->with('status', 'Theme reset to the default from the app configuration.');
    }

    private function deleteUpload(string $path): void
    {
        Storage::disk('public')->delete(Str::after($path, 'storage/'));
    }
}
