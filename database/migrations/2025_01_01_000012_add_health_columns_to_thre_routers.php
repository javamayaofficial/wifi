<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('thre_routers', function (Blueprint $table) {
            $table->boolean('is_up')->default(true)->after('use_tls');
            $table->timestamp('last_checked_at')->nullable()->after('is_up');
            $table->timestamp('down_since')->nullable()->after('last_checked_at');
            $table->timestamp('alert_sent_at')->nullable()->after('down_since');
        });
    }

    public function down(): void
    {
        Schema::table('thre_routers', function (Blueprint $table) {
            $table->dropColumn(['is_up', 'last_checked_at', 'down_since', 'alert_sent_at']);
        });
    }
};
