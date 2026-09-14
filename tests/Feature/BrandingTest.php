<?php

namespace Tests\Feature;

use App\Support\Branding;
use Tests\TestCase;

/**
 * Guards the template's rebranding contract: changing config/branding.php
 * (or the matching .env keys) must change the rendered UI, without any
 * Blade file or stylesheet being edited.
 */
class BrandingTest extends TestCase
{
    /**
     * These tests are about config/branding.php, so the theme saved under
     * Settings > Theme Setting (which would win over it) is taken out of play.
     */
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Cache::forever(\App\Models\ThemeSetting::CACHE_KEY, ['preset' => null, 'colors' => []]);
    }

    public function test_colour_tokens_are_emitted_as_css_variables(): void
    {
        $this->saveTheme(null, ['primary' => '#ABCDEF']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('--brand-primary: #ABCDEF;', false);
    }

    public function test_a_preset_supplies_tokens_when_no_colour_is_changed(): void
    {
        $this->saveTheme('indigo', []);

        $this->assertSame('#6366F1', Branding::colors()['primary']);
    }

    public function test_a_colour_saved_in_theme_setting_beats_the_preset(): void
    {
        $this->saveTheme('indigo', ['primary' => '#123456']);

        $this->assertSame('#123456', Branding::colors()['primary']);
    }

    public function test_env_colour_keys_no_longer_change_the_theme(): void
    {
        config()->set('branding.colors.primary', '#ABCDEF');

        $this->assertNotSame('#ABCDEF', Branding::colors()['primary']);
    }

    /**
     * Stands in for a saved Theme Setting row without touching the database.
     *
     * @param  array<string, string>  $colors
     */
    private function saveTheme(?string $preset, array $colors): void
    {
        \Illuminate\Support\Facades\Cache::forever(\App\Models\ThemeSetting::CACHE_KEY, ['preset' => $preset, 'colors' => $colors]);
    }

    public function test_the_logo_is_rendered_from_config(): void
    {
        config()->set('branding.logo', 'images/custom-logo.svg');

        $this->get('/login')
            ->assertOk()
            ->assertSee('images/custom-logo.svg', false);
    }

    public function test_logo_slots_fall_back_to_the_main_logo(): void
    {
        config()->set('branding.logo', 'images/only-logo.svg');
        config()->set('branding.logo_login', null);
        config()->set('branding.logo_sidebar', null);

        $this->assertStringContainsString('images/only-logo.svg', Branding::logo('login'));
        $this->assertStringContainsString('images/only-logo.svg', Branding::logo('sidebar'));
    }

    public function test_absolute_asset_urls_are_left_untouched(): void
    {
        config()->set('branding.logo', 'https://cdn.example.com/logo.svg');

        $this->assertSame('https://cdn.example.com/logo.svg', Branding::logo());
    }

    public function test_the_background_image_can_be_disabled(): void
    {
        config()->set('branding.background.image', '');
        config()->set('branding.background.colour', '#101010');

        $styles = Branding::backgroundStyles();

        $this->assertArrayNotHasKey('background-image', $styles);
        $this->assertSame('#101010', $styles['background-color']);
    }

    public function test_an_overlay_is_layered_over_the_background_image(): void
    {
        config()->set('branding.background.image', 'images/bg.jpg');
        config()->set('branding.background.overlay', 'rgba(0, 0, 0, 0.5)');

        $this->assertStringContainsString(
            'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5))',
            Branding::backgroundStyles()['background-image']
        );
    }

    public function test_no_font_request_is_emitted_when_google_fonts_is_empty(): void
    {
        config()->set('branding.fonts.google_fonts', '');

        $this->assertNull(Branding::googleFontsUrl());
        $this->get('/login')->assertDontSee('fonts.googleapis.com', false);
    }

    public function test_multiple_google_font_families_are_supported(): void
    {
        config()->set('branding.fonts.google_fonts', 'Inter:wght@400|Poppins:wght@700');

        $this->assertSame(
            'https://fonts.googleapis.com/css2?family=Inter:wght@400&family=Poppins:wght@700&display=swap',
            Branding::googleFontsUrl()
        );
    }

    public function test_css_values_cannot_break_out_of_the_style_block(): void
    {
        $css = Branding::cssDeclarations(['color' => '#fff; } body { display: none']);

        $this->assertStringNotContainsString('}', $css);
        $this->assertStringNotContainsString(';', rtrim($css, ';'));
    }

    public function test_the_copyright_line_defaults_to_the_company_name(): void
    {
        config()->set('branding.company', 'Acme Sdn Bhd');
        config()->set('branding.copyright', '');

        $this->assertSame(
            '© Copyright '.date('Y').' Acme Sdn Bhd. All Rights Reserved.',
            Branding::copyright()
        );
    }
}
