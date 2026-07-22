<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('customer_id')->constrained('thre_customers');
            $table->decimal('amount', 12, 2);
            $table->decimal('amount_final', 12, 2);     // amount + unique_code (Moota)
            $table->string('payment_method');           // doku | moota | manual
            $table->enum('status', ['pending', 'paid', 'expired', 'failed'])->default('pending');
            $table->json('raw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_transactions');
    }
};
