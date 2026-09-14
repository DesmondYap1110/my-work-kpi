@extends('layouts.app')

@section('title', 'Theme Setting')

@section('content')
    {{--
        Settings > Theme Setting. Picking a preset or a colour previews it on this
        page straight away (the --brand-* variables on :root); nothing changes for
        anyone else until Save. See ThemeSettingController and Branding::colors().
    --}}
    <form method="POST" action="{{ route('theme-setting.update') }}" id="theme-form" enctype="multipart/form-data" data-presets='@json($presets->keyBy('key')->map->colors)'>
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

            {{-- The phone bottom menu, as it looks with these colours. --}}
            <div class="theme-preview-phone" aria-hidden="true">
                <span class="theme-preview-phone-label">On phones</span>
                <div class="theme-preview-mobile">
                    <i class="ri-user-3-line"></i>
                    <i class="ri-clipboard-line"></i>
                    <span class="theme-preview-mobile-active"><i class="ri-dashboard-2-line"></i></span>
                    <i class="ri-bar-chart-2-line"></i>
                    <i class="ri-survey-line"></i>
                </div>
            </div>
        </div>

        {{-- Login page background: an image (built-in or uploaded) or a plain
             colour, darkened by the overlay so the form stays readable. --}}
        @php
            $currentImage = (string) ($login['image'] ?? '');
            $selectedImage = old('login_image', match (true) {
                $currentImage === '' => 'none',
                $loginUploaded !== null && $currentImage === $loginUploaded => 'uploaded',
                in_array($currentImage, $loginImages, true) => $currentImage,
                default => $loginImages[0],
            });
            $loginColor = strtoupper(old('login_color', $login['colour'] ?? '#0B0A1F'));
            $overlay = (int) old('login_overlay', $loginOverlay);
        @endphp
        <div id="form-box" class="general-box">
            <div class="appraisal-section-head">
                <p id="form-sub-title" class="mb-0">Login Page</p>
                <span class="kpi-item-desc">Background behind the sign-in form</span>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <label class="theme-field-label">Background image</label>
                    <div class="login-bg-options">
                        @foreach ($loginImages as $image)
                            <label class="login-bg-option">
                                <input type="radio" name="login_image" value="{{ $image }}" data-src="{{ asset($image) }}" @checked($selectedImage === $image)>
                                <span class="login-bg-thumb" style="background-image: url('{{ asset($image) }}')"></span>
                            </label>
                        @endforeach
                        @if ($loginUploaded)
                            <label class="login-bg-option">
                                <input type="radio" name="login_image" value="uploaded" data-src="{{ asset($loginUploaded) }}" @checked($selectedImage === 'uploaded')>
                                <span class="login-bg-thumb" style="background-image: url('{{ asset($loginUploaded) }}')"><em>Uploaded</em></span>
                            </label>
                        @endif
                        <label class="login-bg-option">
                            <input type="radio" name="login_image" value="upload" data-upload @checked($selectedImage === 'upload')>
                            <span class="login-bg-thumb is-upload"><i class="ri-upload-cloud-2-line"></i><em>Upload</em></span>
                        </label>
                        <label class="login-bg-option">
                            <input type="radio" name="login_image" value="none" @checked($selectedImage === 'none')>
                            <span class="login-bg-thumb is-none"><i class="ri-paint-fill"></i><em>Colour only</em></span>
                        </label>
                    </div>

                    <div class="login-bg-upload" data-upload-field @if ($selectedImage !== 'upload') hidden @endif>
                        <input type="file" class="form-control" name="login_upload" accept="image/jpeg,image/png,image/webp" aria-label="Login background image file">
                        <span id="note-p" class="d-block appraisal-modal-note">JPG, PNG or WebP, up to 4 MB, at least 800 x 450 px. A wide photo (1920 x 1080) looks best.</span>
                        @error('login_upload')
                            <span class="unique-check-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="input-group theme-color-field">
                                <label for="login-color">Background colour</label>
                                <div class="theme-color-row">
                                    <input type="color" id="login-color" class="theme-color-picker" value="{{ strtolower($loginColor) }}" data-login-color>
                                    <input type="text" class="form-control theme-color-hex" name="login_color" value="{{ $loginColor }}" maxlength="7" pattern="#[0-9A-Fa-f]{6}" data-login-color-hex aria-label="Login background colour hex value">
                                </div>
                                @error('login_color')
                                    <span class="unique-check-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <label for="login-overlay">Darken image <span class="login-overlay-value" data-overlay-value>{{ $overlay }}%</span></label>
                                <input type="range" id="login-overlay" class="login-overlay-range" name="login_overlay" min="0" max="80" step="5" value="{{ $overlay }}" data-login-overlay>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <label class="theme-field-label">Preview</label>
                    <div class="login-preview" data-login-preview
                         style="background-color: {{ $loginColor }}; @if ($currentImage !== '') background-image: url('{{ asset($currentImage) }}'); @endif">
                        <span class="login-preview-overlay" data-login-preview-overlay style="background: rgba(0,0,0,{{ $overlay / 100 }})"></span>
                        <span class="login-preview-card">
                            <span class="login-preview-logo"></span>
                            <span class="login-preview-input"></span>
                            <span class="login-preview-input"></span>
                            <span class="login-preview-button"></span>
                        </span>
                    </div>
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

            // Login page preview.
            var preview = form.querySelector('[data-login-preview]');
            var previewOverlay = form.querySelector('[data-login-preview-overlay]');
            var uploadField = form.querySelector('[data-upload-field]');
            var fileInput = form.querySelector('input[name="login_upload"]');
            var uploadUrl = null;

            function paintLogin() {
                var picked = form.querySelector('input[name="login_image"]:checked');
                var value = picked ? picked.value : 'none';
                uploadField.hidden = value !== 'upload';
                var src = value === 'upload' ? uploadUrl : (value === 'none' ? null : picked.getAttribute('data-src'));
                preview.style.backgroundImage = src ? 'url("' + src + '")' : 'none';
            }

            form.querySelectorAll('input[name="login_image"]').forEach(function (r) { r.addEventListener('change', paintLogin); });
            fileInput.addEventListener('change', function () {
                if (uploadUrl) URL.revokeObjectURL(uploadUrl);
                uploadUrl = fileInput.files[0] ? URL.createObjectURL(fileInput.files[0]) : null;
                paintLogin();
            });

            var colorPicker = form.querySelector('[data-login-color]');
            var colorHex = form.querySelector('[data-login-color-hex]');
            colorPicker.addEventListener('input', function () {
                colorHex.value = colorPicker.value.toUpperCase();
                preview.style.backgroundColor = colorPicker.value;
            });
            colorHex.addEventListener('input', function () {
                if (/^#[0-9A-Fa-f]{6}$/.test(colorHex.value)) {
                    colorPicker.value = colorHex.value.toLowerCase();
                    preview.style.backgroundColor = colorHex.value;
                }
            });

            var overlay = form.querySelector('[data-login-overlay]');
            overlay.addEventListener('input', function () {
                previewOverlay.style.background = 'rgba(0,0,0,' + (overlay.value / 100) + ')';
                form.querySelector('[data-overlay-value]').textContent = overlay.value + '%';
            });

            paintLogin();

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
