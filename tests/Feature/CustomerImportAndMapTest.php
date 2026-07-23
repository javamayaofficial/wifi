<?php

namespace Tests\Feature;

use App\Imports\CustomersImport;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CustomerImportAndMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_detects_common_header_aliases_and_creates_customer(): void
    {
        [$plan, $router] = $this->seedPlanAndRouter();

        $import = new CustomersImport();
        $import->collection(new Collection([
            new Collection([
                'Nama Pelanggan',
                'PPPoE',
                'Sandi',
                'Profile',
                'Server',
                'Jatuh Tempo',
                'Status Layanan',
                'Nomor WhatsApp',
                'E_mail',
                'Alamat Pelanggan',
                'NIK',
                'Lat',
                'Lng',
            ]),
            new Collection([
                'Budi Santoso',
                'budi-pppoe',
                'rahasia123',
                $plan->mikrotik_profile,
                $router->ip,
                '2026-08-10',
                'aktif',
                '081234567890',
                'budi@example.com',
                'Jl. Melati No. 10',
                '3201123456789012',
                '-6.21234',
                '106.84567',
            ]),
        ]));

        $this->assertSame(1, $import->successCount);
        $this->assertSame([], $import->errors);
        $this->assertSame('name', $import->detectedColumns[0]);
        $this->assertSame('username', $import->detectedColumns[1]);
        $this->assertSame('plan', $import->detectedColumns[3]);
        $this->assertSame('router', $import->detectedColumns[4]);

        $customer = Customer::query()->firstOrFail();

        $this->assertSame('Budi Santoso', $customer->name);
        $this->assertSame('budi-pppoe', $customer->username);
        $this->assertSame($plan->id, $customer->plan_id);
        $this->assertSame($router->id, $customer->router_id);
        $this->assertSame('active', $customer->status);
        $this->assertSame('081234567890', $customer->phone);
        $this->assertSame('budi@example.com', $customer->email);
        $this->assertSame('Jl. Melati No. 10', $customer->address);
        $this->assertSame('3201123456789012', $customer->national_id_number);
        $this->assertSame('-6.2123400', (string) $customer->latitude);
        $this->assertSame('106.8456700', (string) $customer->longitude);
    }

    public function test_dashboard_shows_mapped_customer_identity_summary(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'role' => 'admin',
        ]);

        [$plan, $router] = $this->seedPlanAndRouter();
        $customer = Customer::create([
            'name' => 'Siti Aminah',
            'username' => 'siti-pppoe',
            'password' => 'abc12345',
            'plan_id' => $plan->id,
            'router_id' => $router->id,
            'expired_date' => '2026-08-12',
            'status' => 'active',
            'latitude' => -6.20123,
            'longitude' => 106.81234,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText('Titik Terpetakan');
        $response->assertSeeText('Siti Aminah');
        $response->assertSeeText('ID pelanggan #' . $customer->id . ' · ID MikroTik/PPPoE ' . $customer->username);
    }

    public function test_map_page_shows_name_system_id_and_mikrotik_id(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'role' => 'admin',
        ]);

        [$plan, $router] = $this->seedPlanAndRouter();
        $customer = Customer::create([
            'name' => 'Rina Marlina',
            'username' => 'rina-pppoe',
            'password' => 'abc12345',
            'plan_id' => $plan->id,
            'router_id' => $router->id,
            'expired_date' => '2026-08-15',
            'status' => 'active',
            'address' => 'Jl. Mawar No. 7',
            'latitude' => -6.22111,
            'longitude' => 106.80111,
        ]);

        $response = $this->actingAs($user)->get('/map');

        $response->assertOk();
        $response->assertSeeText('Daftar Titik Pelanggan');
        $response->assertSeeText('Rina Marlina');
        $response->assertSeeText('#' . $customer->id . ' · MikroTik ' . $customer->username);
        $response->assertSee('ID pelanggan: #' . $customer->id, false);
        $response->assertSee('ID MikroTik/PPPoE: <code>' . $customer->username . '</code>', false);
    }

    protected function seedPlanAndRouter(): array
    {
        $plan = Plan::create([
            'name' => 'Paket 20 Mbps',
            'price' => 150000,
            'bandwidth' => '20M/20M',
            'duration_days' => 30,
            'mikrotik_profile' => 'paket20',
        ]);

        $router = Router::create([
            'name' => 'Router Utama',
            'ip' => '10.10.10.1',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'router-secret',
            'use_tls' => false,
        ]);

        return [$plan, $router];
    }
}
