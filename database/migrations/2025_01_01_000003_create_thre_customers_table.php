<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();       // = PPPoE secret name
            $table->text('password');                   // PPPoE password, encrypted via cast
            $table->foreignId('plan_id')->constrained('thre_plans');
            $table->foreignId('router_id')->constrained('thre_routers');
            $table->date('expired_date');
            $table->enum('status', ['new', 'active', 'isolated', 'suspended'])->default('new');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('unique_code')->nullable(); // untuk pencocokan Moota
            $table->timestamps();

            $table->index(['expired_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_customers');
    }
};
