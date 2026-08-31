<?php

/*
|--------------------------------------------------------------------------
| Branding
|--------------------------------------------------------------------------
|
| Everything that makes this install look like a particular product lives
| here: identity, logos, colours, typography and the login background. The
| core application never hardcodes any of it, so rebranding a new project
| means editing this file (or the matching .env keys) and nothing else.
|
| Colours are emitted as CSS custom properties on :root by the
| <x-branding-styles /> component, and the stylesheets in public/assets/css
| consume them as var(--brand-*, #fallback). The fallback is the original
| theme value, so every sheet still renders correctly on its own.
|
| See the "Rebranding This Template" section of README.md.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Identity
    |--------------------------------------------------------------------------
    |
    | Text shown to users. 'name' intentionally falls back to APP_NAME so
    | there is a single obvious place to set the product name.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),
    'short_name' => env('APP_SHORT_NAME', env('APP_NAME', 'Laravel')),
    'company' => env('APP_COMPANY_NAME', env('APP_NAME', 'Laravel')),
    'tagline' => env('APP_TAGLINE', ''),
    'description' => env('APP_DESCRIPTION', ''),
    'copyright' => env('APP_COPYRIGHT', ''),
    'support_email' => env('APP_SUPPORT_EMAIL', ''),
    'website_url' => env('APP_WEBSITE_URL', ''),

    'social' => [
        'facebook' => env('APP_SOCIAL_FACEBOOK', ''),
        'instagram' => env('APP_SOCIAL_INSTAGRAM', ''),
        'linkedin' => env('APP_SOCIAL_LINKEDIN', ''),
        'twitter' => env('APP_SOCIAL_TWITTER', ''),
        'youtube' => env('APP_SOCIAL_YOUTUBE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logos & icons
    |--------------------------------------------------------------------------
    |
    | Paths are relative to public/, or absolute URLs. Each slot falls back
    | to the one above it, so a project that only supplies APP_LOGO gets a
    | usable result everywhere without setting six keys.
    |
    */

    'logo' => env('APP_LOGO', 'images/logo-light.svg'),
    'logo_light' => env('APP_LOGO_LIGHT', null),
    'logo_dark' => env('APP_LOGO_DARK', 'images/logo-dark.svg'),
    'logo_small' => env('APP_LOGO_SMALL', 'images/logo-sm.svg'),
    'logo_login' => env('APP_LOGO_LOGIN', null),
    'logo_sidebar' => env('APP_LOGO_SIDEBAR', null),
    'logo_navbar' => env('APP_LOGO_NAVBAR', null),
    'logo_mobile' => env('APP_LOGO_MOBILE', null),

    'favicon' => env('APP_FAVICON', 'images/logo-sm.svg'),
    'apple_touch_icon' => env('APP_APPLE_TOUCH_ICON', null),
    'og_image' => env('APP_OG_IMAGE', null),

    /*
    |--------------------------------------------------------------------------
    | Login background
    |--------------------------------------------------------------------------
    |
    | Set 'image' to an empty string to drop the image and fall back to the
    | flat 'colour' below it.
    |
    */

    'background' => [
        'image' => env('APP_BACKGROUND_IMAGE', 'assets/img/bg/bg-3.jpg'),
        'colour' => env('APP_BACKGROUND_COLOR', '#0b0a1f'),
        'size' => env('APP_BACKGROUND_SIZE', 'cover'),
        'position' => env('APP_BACKGROUND_POSITION', 'center'),
        'repeat' => env('APP_BACKGROUND_REPEAT', 'no-repeat'),
        'attachment' => env('APP_BACKGROUND_ATTACHMENT', 'fixed'),
        'overlay' => env('APP_BACKGROUND_OVERLAY', 'rgba(0, 0, 0, 0)'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Typography
    |--------------------------------------------------------------------------
    |
    | 'google_fonts' is the families query for fonts.googleapis.com. Leave it
    | empty to load nothing (e.g. when self-hosting or using system fonts) -
    | no stray network request is emitted in that case.
    |
    */

    'fonts' => [
        'body' => env('APP_FONT_FAMILY', "'Roboto', sans-serif"),
        'heading' => env('APP_FONT_HEADING', null),
        'google_fonts' => env('APP_GOOGLE_FONTS', 'Roboto:wght@300;400;500;600;700;800'),
        'base_size' => env('APP_FONT_SIZE', '13px'),
        'weight_normal' => env('APP_FONT_WEIGHT_NORMAL', '400'),
        'weight_bold' => env('APP_FONT_WEIGHT_BOLD', '600'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Active theme preset
    |--------------------------------------------------------------------------
    |
    | Names a key from 'presets' below. Individual APP_*_COLOR env vars still
    | win over whatever the preset supplies, so a project can pick a preset
    | and then tweak one or two tokens.
    |
    */

    'theme' => env('APP_THEME', 'default'),
    'mode' => env('APP_THEME_MODE', 'dark'),

    /*
    |--------------------------------------------------------------------------
    | Colour tokens
    |--------------------------------------------------------------------------
    |
    | Each key becomes a --brand-<key> custom property. Anything left null
    | falls through to the active preset, and then to the stylesheet's own
    | fallback value.
    |
    */

    'colors' => [
        'primary' => env('APP_PRIMARY_COLOR'),
        'primary-hover' => env('APP_PRIMARY_HOVER_COLOR'),
        'secondary' => env('APP_SECONDARY_COLOR'),
        'secondary-hover' => env('APP_SECONDARY_HOVER_COLOR'),
        'accent' => env('APP_ACCENT_COLOR'),
        'highlight' => env('APP_HIGHLIGHT_COLOR'),

        'background' => env('APP_BODY_COLOR'),
        'surface' => env('APP_SURFACE_COLOR'),
        'sidebar' => env('APP_SIDEBAR_COLOR'),
        'navbar' => env('APP_NAVBAR_COLOR'),

        'text' => env('APP_TEXT_COLOR'),
        'text-muted' => env('APP_TEXT_MUTED_COLOR'),
        'label' => env('APP_LABEL_COLOR'),
        'link' => env('APP_LINK_COLOR'),
        'border' => env('APP_BORDER_COLOR'),

        'button' => env('APP_BUTTON_COLOR'),
        'button-hover' => env('APP_BUTTON_HOVER_COLOR'),
        'button-text' => env('APP_BUTTON_TEXT_COLOR'),

        'input-bg' => env('APP_INPUT_BG_COLOR'),
        'input-text' => env('APP_INPUT_TEXT_COLOR'),
        'input-border' => env('APP_INPUT_BORDER_COLOR'),
        'input-focus' => env('APP_INPUT_FOCUS_COLOR'),

        'success' => env('APP_SUCCESS_COLOR'),
        'warning' => env('APP_WARNING_COLOR'),
        'danger' => env('APP_DANGER_COLOR'),
        'danger-hover' => env('APP_DANGER_HOVER_COLOR'),
        'info' => env('APP_INFO_COLOR'),
        'pink' => env('APP_PINK_COLOR'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme presets
    |--------------------------------------------------------------------------
    |
    | 'default' reproduces the shipped theme exactly. Add a preset by copying
    | it and changing the values; select it with APP_THEME.
    |
    */

    'presets' => [

        'default' => [
            'primary' => '#1896bd',
            'primary-hover' => '#147796',
            'secondary' => '#012161',
            'secondary-hover' => '#011844',
            'accent' => '#24EBC5',
            'highlight' => '#F19820',
            'background' => '#eaedf7',
            'surface' => '#ffffff',
            'sidebar' => '#000f2d',
            'navbar' => '#ffffff',
            'text' => '#282828',
            'text-muted' => '#6e6e6e',
            'label' => '#737c91',
            'link' => '#00b7ff',
            'border' => '#e9e9e9',
            'button' => '#1896bd',
            'button-hover' => '#147796',
            'button-text' => '#ffffff',
            'input-bg' => 'transparent',
            'input-text' => '#282828',
            'input-border' => '#e9e9e9',
            'input-focus' => '#1896bd',
            'success' => '#00d230',
            'warning' => '#e18d00',
            'danger' => '#ff4d4d',
            'danger-hover' => '#ff1c1c',
            'info' => '#00b7ff',
            'pink' => '#e54b90',
        ],

        'indigo' => [
            'primary' => '#6366F1',
            'primary-hover' => '#4F46E5',
            'secondary' => '#312E81',
            'secondary-hover' => '#1E1B4B',
            'accent' => '#EC4899',
            'highlight' => '#F59E0B',
            'background' => '#F8FAFC',
            'surface' => '#ffffff',
            'sidebar' => '#1E1B4B',
            'navbar' => '#ffffff',
            'text' => '#1E293B',
            'text-muted' => '#64748B',
            'label' => '#64748B',
            'link' => '#6366F1',
            'border' => '#E2E8F0',
            'button' => '#6366F1',
            'button-hover' => '#4F46E5',
            'button-text' => '#ffffff',
            'input-bg' => 'transparent',
            'input-text' => '#1E293B',
            'input-border' => '#E2E8F0',
            'input-focus' => '#6366F1',
            'success' => '#10B981',
            'warning' => '#F59E0B',
            'danger' => '#EF4444',
            'danger-hover' => '#DC2626',
            'info' => '#3B82F6',
            'pink' => '#EC4899',
        ],

        'forest' => [
            'primary' => '#059669',
            'primary-hover' => '#047857',
            'secondary' => '#064E3B',
            'secondary-hover' => '#022C22',
            'accent' => '#84CC16',
            'highlight' => '#F59E0B',
            'background' => '#F0FDF4',
            'surface' => '#ffffff',
            'sidebar' => '#022C22',
            'navbar' => '#ffffff',
            'text' => '#1F2937',
            'text-muted' => '#6B7280',
            'label' => '#6B7280',
            'link' => '#059669',
            'border' => '#D1FAE5',
            'button' => '#059669',
            'button-hover' => '#047857',
            'button-text' => '#ffffff',
            'input-bg' => 'transparent',
            'input-text' => '#1F2937',
            'input-border' => '#D1FAE5',
            'input-focus' => '#059669',
            'success' => '#10B981',
            'warning' => '#F59E0B',
            'danger' => '#EF4444',
            'danger-hover' => '#DC2626',
            'info' => '#0EA5E9',
            'pink' => '#DB2777',
        ],

        'slate' => [
            'primary' => '#0EA5E9',
            'primary-hover' => '#0284C7',
            'secondary' => '#1E293B',
            'secondary-hover' => '#0F172A',
            'accent' => '#38BDF8',
            'highlight' => '#FB923C',
            'background' => '#F1F5F9',
            'surface' => '#ffffff',
            'sidebar' => '#0F172A',
            'navbar' => '#ffffff',
            'text' => '#0F172A',
            'text-muted' => '#64748B',
            'label' => '#64748B',
            'link' => '#0EA5E9',
            'border' => '#E2E8F0',
            'button' => '#0EA5E9',
            'button-hover' => '#0284C7',
            'button-text' => '#ffffff',
            'input-bg' => 'transparent',
            'input-text' => '#0F172A',
            'input-border' => '#E2E8F0',
            'input-focus' => '#0EA5E9',
            'success' => '#22C55E',
            'warning' => '#F59E0B',
            'danger' => '#EF4444',
            'danger-hover' => '#DC2626',
            'info' => '#0EA5E9',
            'pink' => '#EC4899',
        ],

    ],

];
