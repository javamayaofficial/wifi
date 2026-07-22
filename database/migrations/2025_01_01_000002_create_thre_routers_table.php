<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('thre_routers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip');
            $table->unsignedInteger('api_port')->default(8728);
            $table->string('username');
            $table->text('password');               // encrypted via model cast
            $table->boolean('use_tls')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thre_routers');
    }
};
