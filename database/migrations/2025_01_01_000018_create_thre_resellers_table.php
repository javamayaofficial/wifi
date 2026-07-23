<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_resellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('area')->nullable();
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->decimal('deposit_balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('thre_reseller_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained('thre_resellers')->cascadeOnDelete();
            $table->enum('type', ['deposit', 'komisi', 'penarikan', 'penyesuaian']);
            $table->decimal('amount', 14, 2);            // positif = menambah saldo
            $table->decimal('balance_after', 14, 2);
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_reseller_transactions');
        Schema::dropIfExists('thre_resellers');
    }
};
