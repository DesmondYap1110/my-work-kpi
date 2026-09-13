@extends('layouts.app')

@section('title', 'Theme Setting')

@section('content')
    {{--
        Settings > Theme Setting. Picking a preset or a colour previews it on this
        page straight away (the --brand-* variables on :root); nothing changes for
        anyone else until Save. See ThemeSettingController and Branding::colors().
    --}}
    <form method="POST" action="{{ route('theme-setting.update') }}" id="theme-form" data-presets='@json($presets->keyBy('key')->map->colors)'>
        @csrf @method('PUT')

        <div id="form-box" class="general-box">
            <div class="appraisal-section-head">
                <p id="form-sub-title" class="mb-0 d-flex align-items-center gap-2">
                    Theme
                    <button type="button" class="kpi-help-btn" aria-label="How does the theme work?"
                            data-help-hover="Pick a theme, then change any of its main colours below if you like. This page previews your choice as you go; everyone sees it once you save.">
                        <i class="ri-question-line"></i>
                    </button>
                </p>
                <span class="kpi-item-desc">Preview updates as you choose</span>
            </div>

            <div class="theme-presets">
                @foreach ($presets as $preset)
                    <label class="theme-preset">
                        <input type="radio" name="preset" value="{{ $preset['key'] }}" @checked($activePreset === $preset['key'])>
                        <span class="theme-preset-card">
                            <span class="theme-preset-swatches">
                                @foreach (['sidebar', 'secondary', 'primary', 'accent', 'background'] as $token)
                                    <span style="background: {{ $preset['colors'][$token] ?? '#ccc' }}" title="{{ ucfirst($token) }}"></span>
                                @endforeach
                            </span>
                            <span class="theme-preset-name">
                                <i class="ri-checkbox-circle-fill"></i>{{ $preset['label'] }}
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div id="form-box" class="general-box">
            <div class="appraisal-section-head">
                <p id="form-sub-title" class="mb-0">Colours</p>
                <span class="kpi-item-desc">Leave a colour as the theme's own, or pick your own</span>
            </div>

            <div class="row">
                @foreach ($editable as $token => $label)
                    @php $value = $custom[$token] ?? ($current[$token] ?? '#000000'); @endphp
                    <div class="col-lg-4 col-md-6">
                        <div class="input-group theme-color-field">
                            <label for="theme-{{ $token }}">{{ $label }}</label>
                            <div class="theme-color-row">
                                <input type="color" id="theme-{{ $token }}" class="theme-color-picker" value="{{ $value }}" data-token="{{ $token }}" aria-label="{{ $label }}">
                                <input type="text" class="form-control theme-color-hex" name="colors[{{ $token }}]" value="{{ strtoupper($value) }}"
                                       maxlength="7" pattern="#[0-9A-Fa-f]{6}" data-token="{{ $token }}" aria-label="{{ $label }} hex value">
                                <button type="button" class="theme-color-reset" data-token="{{ $token }}" title="Use the theme's own colour">
                                    <i class="ri-arrow-go-back-line"></i>
                                </button>
                            </div>
                            @error("colors.$token")
                                <span class="unique-check-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- What the colours are used for, drawn from the preview variables. --}}
            <div class="theme-preview">
                <div class="theme-preview-sidebar">
                    <span class="theme-preview-logo"></span>
                    <span class="theme-preview-item is-active">Dashboard</span>
                    <span class="theme-preview-item">Appraisal</span>
                </div>
                <div class="theme-preview-body">
                    <p class="theme-preview-title">Page title</p>
                    <div class="theme-preview-table">
                        <span>Member</span><span>Score</span>
                    </div>
                    <p class="theme-preview-text">Body text and a <a href="#" onclick="return false">link</a>.</p>
                    <span class="theme-preview-btn">Button</span>
                </div>
            </div>
        </div>

        <div id="form-box" class="general-box appraisal-actions">
            <div class="appraisal-actions-status">
                <span>Saved themes apply to every user on their next page load.</span>
            </div>
            <div class="appraisal-actions-buttons">
                <button type="submit" form="theme-reset-form" id="general-btn" class="btn2"
                        data-confirm-title="Reset theme"
                        data-confirm="Go back to the default theme from the app configuration? Your chosen theme and colours are removed."
                        data-confirm-label="Reset"
                        data-confirm-icon="ri-arrow-go-back-line">
                    <i class="ri-arrow-go-back-line"></i>Reset to Default
                </button>
                <a href="{{ route('theme-setting.edit') }}" id="general-btn" class="btn2"><i class="ri-close-line"></i>Discard Changes</a>
                <button type="submit" id="general-btn" class="btn1"><i class="ri-save-3-line"></i>Save Theme</button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('theme-setting.reset') }}" id="theme-reset-form">
        @csrf @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            var form = document.getElementById('theme-form');
            var presets = JSON.parse(form.getAttribute('data-presets'));
            var root = document.documentElement.style;

            function darken(hex, percent) {
                var f = 1 - percent / 100;
                return '#' + hex.replace('#', '').match(/../g).map(function (p) {
                    return ('0' + Math.round(parseInt(p, 16) * f).toString(16)).slice(-2);
                }).join('');
            }

            // The same followers as Branding::expandCustomColors().
            function apply(token, value) {
                if (!/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    return;
                }
                root.setProperty('--brand-' + token, value);
                if (token === 'primary') {
                    ['button', 'input-focus', 'link'].forEach(function (t) { root.setProperty('--brand-' + t, value); });
                    root.setProperty('--brand-primary-hover', darken(value, 15));
                    root.setProperty('--brand-button-hover', darken(value, 15));
                }
                if (token === 'secondary') {
                    root.setProperty('--brand-secondary-hover', darken(value, 15));
                }
            }

            function presetColors() {
                var picked = form.querySelector('input[name="preset"]:checked');
                return presets[picked ? picked.value : Object.keys(presets)[0]] || {};
            }

            function setField(token, value) {
                form.querySelectorAll('[data-token="' + token + '"]').forEach(function (el) {
                    if (el.classList.contains('theme-color-picker')) el.value = value.toLowerCase();
                    if (el.classList.contains('theme-color-hex')) el.value = value.toUpperCase();
                });
                apply(token, value);
            }

            // A new preset: preview all its colours and reset the fields to it.
            form.querySelectorAll('input[name="preset"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    var colors = presetColors();
                    Object.keys(colors).forEach(function (token) { root.setProperty('--brand-' + token, colors[token]); });
                    form.querySelectorAll('.theme-color-hex').forEach(function (input) {
                        var token = input.getAttribute('data-token');
                        if (colors[token]) setField(token, colors[token]);
                    });
                });
            });

            form.addEventListener('input', function (event) {
                var el = event.target;
                var token = el.getAttribute('data-token');
                if (!token) return;
                if (el.classList.contains('theme-color-picker')) setField(token, el.value);
                if (el.classList.contains('theme-color-hex') && /^#[0-9A-Fa-f]{6}$/.test(el.value)) {
                    form.querySelector('.theme-color-picker[data-token="' + token + '"]').value = el.value.toLowerCase();
                    apply(token, el.value);
                }
            });

            form.addEventListener('click', function (event) {
                var button = event.target.closest('.theme-color-reset');
                if (!button) return;
                var token = button.getAttribute('data-token');
                var colors = presetColors();
                if (colors[token]) setField(token, colors[token]);
            });
        })();
    </script>
@endpush
