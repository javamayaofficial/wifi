<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('thre_transactions', function (Blueprint $table) {
            $table->decimal('late_fee', 12, 2)->default(0)->after('amount');
            $table->decimal('discount', 12, 2)->default(0)->after('late_fee');
            $table->string('note')->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('thre_transactions', function (Blueprint $table) {
            $table->dropColumn(['late_fee', 'discount', 'note']);
        });
    }
};
