<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records why a project was cancelled, by whom and when.
 *
 * Cancelling ends a project and stops it taking new work, and months later the
 * only question anybody asks about a cancelled project is "why?". A status on
 * its own cannot answer that, so the reason is required at the moment of
 * cancelling and kept with the project.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('cancel_reason');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')
                ->constrained('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancel_reason', 'cancelled_at', 'cancelled_by']);
        });
    }
};
