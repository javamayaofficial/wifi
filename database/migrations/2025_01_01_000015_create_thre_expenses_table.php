<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('category');          // bandwidth, listrik, gaji, sewa, perangkat, lainnya
            $table->string('description');
            $table->decimal('amount', 14, 2);
            $table->string('attachment')->nullable();  // foto nota
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_expenses');
    }
};
