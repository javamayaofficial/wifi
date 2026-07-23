<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();   // disalin agar tetap terbaca bila user dihapus
            $table->string('event');                    // created | updated | deleted
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('label')->nullable();        // deskripsi singkat objek
            $table->json('changes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_audit_logs');
    }
};
