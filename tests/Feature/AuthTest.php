<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertSuccessful();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    public function test_superadmin_redirects_to_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertSuccessful();
    }

    public function test_petugas_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertSuccessful();
    }

    public function test_petugas_cannot_access_admin_users(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($user)->get('/admin/pengguna');
        $response->assertForbidden();
    }

    public function test_logout_redirects(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_inactive_user_is_logged_out(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
