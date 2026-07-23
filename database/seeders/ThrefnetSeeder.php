<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data awal agar sistem bisa langsung dipakai setelah migrate.
 * Aman dijalankan berulang (updateOrCreate / firstOrCreate).
 *
 *   php artisan db:seed --class=ThrefnetSeeder
 */
class ThrefnetSeeder extends Seeder
{
    public function run(): void
    {
        // --- Akun owner pertama ---
        $email = env('OWNER_EMAIL', 'owner@thre.f.net');
        $pass  = env('OWNER_PASSWORD', 'ubahsegera');

        $owner = User::firstOrCreate(
            ['email' => $email],
            [
                'name'      => 'Owner THRE.F.NET',
                'password'  => Hash::make($pass),
                'role'      => 'owner',
                'is_active' => true,
            ]
        );

        // --- Pengaturan dasar ---
        $defaults = [
            'active_driver'    => 'manual',   // mulai dari manual, aman tanpa kredensial
            'company_address'  => '',
            'company_phone'    => '',
            'company_email'    => 'info@thre.f.net',
            'hotspot_login_url'=> '',
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::where('key', $key)->doesntExist()) {
                Setting::put($key, $value);
            }
        }

        // --- Contoh paket, supaya form pelanggan tidak kosong ---
        if (Plan::count() === 0) {
            Plan::create([
                'name'             => 'Home 10 Mbps',
                'price'            => 150000,
                'bandwidth'        => '10M/10M',
                'duration_days'    => 30,
                'mikrotik_profile' => 'paket-10m',
            ]);

            Plan::create([
                'name'             => 'Home 20 Mbps',
                'price'            => 200000,
                'bandwidth'        => '20M/20M',
                'duration_days'    => 30,
                'mikrotik_profile' => 'paket-20m',
            ]);
        }

        $this->command->info("Owner  : {$owner->email}");
        $this->command->warn("Password: {$pass}  <- GANTI SEGERA setelah login pertama");
        $this->command->info('Driver pembayaran awal: manual');
    }
}
