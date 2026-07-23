<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Services\Auth\PortalWhatsappOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class WhatsappOtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    public function test_portal_login_screen_shows_whatsapp_otp_option(): void
    {
        $response = $this->get('/portal/login');

        $response
            ->assertOk()
            ->assertSee('Portal Pelanggan')
            ->assertSee('Masuk dengan OTP WhatsApp');
    }

    public function test_customers_can_request_whatsapp_otp_from_portal(): void
    {
        $customer = $this->createCustomer();

        $service = Mockery::mock(PortalWhatsappOtpService::class);
        $service->shouldReceive('requestOtp')
            ->once()
            ->andReturn([
                'ok' => true,
                'masked_phone' => '6281*****89',
            ]);

        $this->app->instance(PortalWhatsappOtpService::class, $service);

        $response = $this->from('/portal/login')->post('/portal/login/request-otp', [
            'otp_phone' => $customer->phone,
        ]);

        $response
            ->assertRedirect('/portal/login')
            ->assertSessionHas('success');
    }

    public function test_customers_can_login_using_whatsapp_otp_from_portal(): void
    {
        $customer = $this->createCustomer();

        $service = Mockery::mock(PortalWhatsappOtpService::class);
        $service->shouldReceive('verifyOtp')
            ->once()
            ->andReturn([
                'ok' => true,
                'customer' => $customer,
            ]);

        $this->app->instance(PortalWhatsappOtpService::class, $service);

        $response = $this->post('/portal/login', [
            'otp_phone' => $customer->phone,
            'otp_code' => '123456',
        ]);

        $response->assertRedirect('/portal');
        $response->assertSessionHas('portal_customer_id', $customer->id);
    }

    protected function createCustomer(): Customer
    {
        $plan = Plan::query()->create([
            'name' => 'Paket Rumahan',
            'price' => 150000,
            'bandwidth' => '20 Mbps',
            'duration_days' => 30,
            'mikrotik_profile' => 'default',
        ]);

        $router = Router::query()->create([
            'name' => 'Router Utama',
            'ip' => '192.168.88.1',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'secret123',
            'use_tls' => false,
        ]);

        return Customer::query()->create([
            'name' => 'Pelanggan Test',
            'username' => 'cust-test',
            'password' => 'pppoe-secret',
            'plan_id' => $plan->id,
            'router_id' => $router->id,
            'expired_date' => now()->addMonth()->toDateString(),
            'status' => 'active',
            'phone' => '08123456789',
        ]);
    }
}
