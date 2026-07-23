<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_voucher_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // mis. "Harian 1 Hari"
            $table->string('hotspot_profile');             // nama profile di MikroTik
            $table->decimal('price', 12, 2)->default(0);
            $table->string('validity')->default('1d');     // limit-uptime / masa berlaku
            $table->unsignedInteger('code_length')->default(6);
            $table->timestamps();
        });

        Schema::create('thre_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_profile_id')->constrained('thre_voucher_profiles')->cascadeOnDelete();
            $table->foreignId('router_id')->constrained('thre_routers');
            $table->string('batch')->index();
            $table->string('code')->unique();
            $table->string('password');
            $table->enum('status', ['tersedia', 'terjual', 'terpakai', 'kadaluarsa'])->default('tersedia');
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_vouchers');
        Schema::dropIfExists('thre_voucher_profiles');
    }
};
