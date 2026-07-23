<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thre_customers', function (Blueprint $table) {
            $table->string('national_id_number', 32)->nullable()->after('address');
            $table->string('identity_card_path')->nullable()->after('installation_photo');
            $table->timestamp('profile_completed_at')->nullable()->after('identity_card_path');
        });
    }

    public function down(): void
    {
        Schema::table('thre_customers', function (Blueprint $table) {
            $table->dropColumn([
                'national_id_number',
                'identity_card_path',
                'profile_completed_at',
            ]);
        });
    }
};
