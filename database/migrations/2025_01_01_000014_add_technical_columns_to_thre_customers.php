<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('thre_customers', function (Blueprint $table) {
            $table->text('address')->nullable()->after('email');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('odp_name')->nullable()->after('longitude');
            $table->string('odp_port')->nullable()->after('odp_name');
            $table->string('device_type')->nullable()->after('odp_port');
            $table->string('device_serial')->nullable()->after('device_type');
            $table->string('installation_photo')->nullable()->after('device_serial');
            $table->date('installed_at')->nullable()->after('installation_photo');
            $table->string('portal_password')->nullable()->after('installed_at'); // bcrypt, untuk portal pelanggan
            $table->foreignId('reseller_id')->nullable()->after('portal_password');

            $table->index('odp_name');
        });
    }

    public function down(): void
    {
        Schema::table('thre_customers', function (Blueprint $table) {
            $table->dropIndex(['odp_name']);
            $table->dropColumn([
                'address', 'latitude', 'longitude', 'odp_name', 'odp_port',
                'device_type', 'device_serial', 'installation_photo',
                'installed_at', 'portal_password', 'reseller_id',
            ]);
        });
    }
};
