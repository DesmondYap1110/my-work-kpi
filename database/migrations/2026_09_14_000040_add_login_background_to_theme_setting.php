<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The login page background, chosen under Settings > Theme Setting instead of
 * APP_BACKGROUND_* in .env. Each column NULL = keep the .env / config value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_setting', function (Blueprint $table) {
            $table->string('login_background_image')->nullable()->after('colors')
                ->comment('Login page background image, a path under public/: a built-in one (assets/img/bg/bg-3.jpg) or an upload (storage/login-backgrounds/<file>). "none" = no image, plain colour only. NULL = APP_BACKGROUND_IMAGE.');
            $table->string('login_background_color', 7)->nullable()->after('login_background_image')
                ->comment('Login page colour behind (or instead of) the image, #RRGGBB. NULL = APP_BACKGROUND_COLOR.');
            $table->unsignedTinyInteger('login_overlay')->nullable()->after('login_background_color')
                ->comment('How much the login image is darkened so the form stays readable, 0-80 (percent black). NULL = APP_BACKGROUND_OVERLAY.');
        });
    }

    public function down(): void
    {
        Schema::table('theme_setting', function (Blueprint $table) {
            $table->dropColumn(['login_background_image', 'login_background_color', 'login_overlay']);
        });
    }
};
