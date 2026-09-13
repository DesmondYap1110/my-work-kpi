<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The colour theme chosen under Settings > Theme Setting. One row, id 1.
 *
 * It sits on top of config/branding.php: the preset named here replaces
 * APP_THEME, and any colour saved here replaces the preset's colour of the
 * same name. With the row empty, the app looks exactly as .env says.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_setting', function (Blueprint $table) {
            $table->id();
            $table->string('preset', 40)->nullable()
                ->comment('Key of a preset in config/branding.php (e.g. light-green, indigo). NULL = use APP_THEME from .env.');
            $table->json('colors')->nullable()
                ->comment('Custom colours over the preset, as {"primary": "#22A65E", "sidebar": "#14532D", ...} - token => #RRGGBB. Only tokens the admin changed. NULL = none, the preset as it is.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_setting');
    }
};
