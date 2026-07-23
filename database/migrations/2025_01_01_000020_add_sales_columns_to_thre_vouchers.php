<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('thre_voucher_profiles', function (Blueprint $table) {
            // Masa simpan sebelum dianggap hangus (0 = tidak pernah hangus).
            $table->unsignedInteger('shelf_life_days')->default(0)->after('validity');
            $table->decimal('agent_price', 12, 2)->default(0)->after('price');
        });

        Schema::table('thre_vouchers', function (Blueprint $table) {
            $table->foreignId('reseller_id')->nullable()->after('router_id')
                ->constrained('thre_resellers')->nullOnDelete();
            $table->decimal('sale_price', 12, 2)->nullable()->after('status');
            $table->foreignId('sold_by')->nullable()->after('sale_price')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('handed_over_at')->nullable()->after('sold_by');

            $table->index(['reseller_id', 'status']);
            $table->index('sold_at');
        });
    }

    public function down(): void
    {
        Schema::table('thre_vouchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reseller_id');
            $table->dropConstrainedForeignId('sold_by');
            $table->dropColumn(['sale_price', 'handed_over_at']);
        });

        Schema::table('thre_voucher_profiles', function (Blueprint $table) {
            $table->dropColumn(['shelf_life_days', 'agent_price']);
        });
    }
};
