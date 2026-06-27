<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPenggunaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_pengguna_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/pengguna');
        $response->assertSuccessful();
    }

    public function test_pengguna_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/pengguna/tambah');
        $response->assertSuccessful();
    }

    public function test_pengguna_store_creates_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/pengguna', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'verifikator',
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/pengguna');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'verifikator',
        ]);
    }

    public function test_pengguna_edit_page_loads(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($this->admin)->get('/admin/pengguna/'.$user->id.'/edit');
        $response->assertSuccessful();
    }

    public function test_pengguna_update_changes_data(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($this->admin)->put('/admin/pengguna/'.$user->id, [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'verifikator',
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/pengguna');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => 'verifikator',
        ]);
    }

    public function test_pengguna_toggle_aktivasi(): void
    {
        $user = User::factory()->create(['role' => 'petugas', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post('/admin/pengguna/'.$user->id.'/toggle');
        $response->assertRedirect();

        $this->assertFalse(User::find($user->id)->is_active);
    }

    public function test_pengguna_destroy_deletes_user(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($this->admin)->delete('/admin/pengguna/'.$user->id);
        $response->assertRedirect();

        $this->assertNull(User::find($user->id));
    }

    public function test_petugas_cannot_access_pengguna(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($petugas)->get('/admin/pengguna');
        $response->assertForbidden();
    }
}
