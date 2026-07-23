<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');              // router, onu, radio, kabel, adaptor, lainnya
            $table->string('serial')->nullable()->unique();
            $table->enum('status', ['gudang', 'terpasang', 'rusak', 'hilang'])->default('gudang');
            $table->foreignId('customer_id')->nullable()->constrained('thre_customers')->nullOnDelete();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 14, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_inventory');
    }
};
