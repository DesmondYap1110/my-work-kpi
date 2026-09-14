<?php

namespace Tests\Unit;

use App\Support\Branding;
use PHPUnit\Framework\TestCase;

/**
 * How the app's colours are decided: the preset, then what an admin saved
 * under Settings > Theme Setting. (There is no .env colour layer.)
 */
class ThemeColorsTest extends TestCase
{
    private const PRESETS = [
        'default' => ['primary' => '#1896BD', 'sidebar' => '#000F2D', 'danger' => '#FF4D4D'],
        'light-green' => ['primary' => '#22A65E', 'sidebar' => '#14532D'],
    ];

    public function test_the_chosen_preset_fills_in_over_the_default(): void
    {
        $colors = Branding::resolveColors(self::PRESETS, 'light-green', []);

        $this->assertSame('#22A65E', $colors['primary']);
        $this->assertSame('#FF4D4D', $colors['danger'], 'A token the preset leaves out comes from the default preset.');
    }

    public function test_a_saved_colour_beats_the_preset(): void
    {
        $colors = Branding::resolveColors(self::PRESETS, 'light-green', ['primary' => '#222222', 'sidebar' => '']);

        $this->assertSame('#222222', $colors['primary']);
        $this->assertSame('#14532D', $colors['sidebar'], 'An empty saved value does not blank the preset.');
    }

    public function test_the_mobile_menu_follows_the_sidebar_unless_given_its_own(): void
    {
        $this->assertSame('#14532D', Branding::resolveColors(self::PRESETS, 'light-green', [])['mobile-menu']);
        $this->assertSame('#0A0A0A', Branding::resolveColors(self::PRESETS, 'light-green', ['mobile-menu' => '#0A0A0A'])['mobile-menu']);
    }

    public function test_an_unknown_preset_falls_back_to_the_default(): void
    {
        $this->assertSame('#1896BD', Branding::resolveColors(self::PRESETS, 'no-such-theme', [])['primary']);
    }

    public function test_a_custom_primary_carries_its_related_colours(): void
    {
        $out = Branding::expandCustomColors(['primary' => '#22A65E']);

        $this->assertSame('#22A65E', $out['button']);
        $this->assertSame('#22A65E', $out['link']);
        $this->assertSame('#22A65E', $out['input-focus']);
        $this->assertSame(Branding::darken('#22A65E', 15), $out['primary-hover']);
    }

    public function test_anything_but_a_hex_colour_is_dropped(): void
    {
        $out = Branding::expandCustomColors(['primary' => 'red; } body { display:none', 'sidebar' => '#12345', 'text' => '#1F2937']);

        $this->assertArrayNotHasKey('primary', $out);
        $this->assertArrayNotHasKey('sidebar', $out);
        $this->assertSame('#1F2937', $out['text']);
    }

    public function test_darken_moves_towards_black(): void
    {
        $this->assertSame('#808080', Branding::darken('#FFFFFF', 50));
        $this->assertSame('#000000', Branding::darken('#22A65E', 100));
        $this->assertSame('#22a65e', Branding::darken('#22A65E', 0));
    }
}
