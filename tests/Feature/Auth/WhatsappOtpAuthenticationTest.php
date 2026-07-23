<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Auth\WhatsappOtpLoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WhatsappOtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_shows_whatsapp_otp_option(): void
    {
        $response = $this->get('/signin');

        $response
            ->assertOk()
            ->assertSee('Masuk dengan OTP WhatsApp');
    }

    public function test_users_can_request_whatsapp_otp(): void
    {
        User::factory()->create([
            'phone' => '08123456789',
        ]);

        $service = Mockery::mock(WhatsappOtpLoginService::class);
        $service->shouldReceive('requestOtp')
            ->once()
            ->andReturn([
                'ok' => true,
                'masked_phone' => '6281*****89',
            ]);

        $this->app->instance(WhatsappOtpLoginService::class, $service);

        $response = $this->from('/signin')->post('/signin/otp/request', [
            'otp_phone' => '08123456789',
        ]);

        $response
            ->assertRedirect('/signin')
            ->assertSessionHas('otp_success');
    }

    public function test_users_can_login_using_whatsapp_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '08123456789',
        ]);

        $service = Mockery::mock(WhatsappOtpLoginService::class);
        $service->shouldReceive('verifyOtp')
            ->once()
            ->andReturn([
                'ok' => true,
                'user' => $user,
            ]);

        $this->app->instance(WhatsappOtpLoginService::class, $service);

        $response = $this->post('/signin/otp/verify', [
            'otp_phone' => '08123456789',
            'otp_code' => '123456',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }
}
