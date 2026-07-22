<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('thre_customers')->nullOnDelete();
            $table->string('type');        // whatsapp | email
            $table->string('channel');     // gateway | mailketing
            $table->string('status');      // sent | failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_notifications_log');
    }
};
