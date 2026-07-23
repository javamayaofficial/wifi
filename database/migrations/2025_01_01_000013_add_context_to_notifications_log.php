<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('thre_notifications_log', function (Blueprint $table) {
            // mis. "reminder_h7", "reminder_h3", "isolir", "payment_success"
            $table->string('context')->nullable()->after('channel');
            $table->index(['customer_id', 'context']);
        });
    }

    public function down(): void
    {
        Schema::table('thre_notifications_log', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'context']);
            $table->dropColumn('context');
        });
    }
};
