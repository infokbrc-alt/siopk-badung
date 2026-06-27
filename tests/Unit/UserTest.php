<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_hashed_password(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);

        $this->assertNotEquals('password', $user->password);
        $this->assertTrue(password_verify('password', $user->password));
    }

    public function test_is_active_casts_to_boolean(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue($user->is_active);
    }

    public function test_is_super_admin_returns_true_for_superadmin(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_is_super_admin_returns_false_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_is_admin_returns_true_for_superadmin(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_true_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_verifikator(): void
    {
        $user = User::factory()->create(['role' => 'verifikator']);
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_verifikator_returns_true_for_superadmin(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->assertTrue($user->isVerifikator());
    }

    public function test_is_verifikator_returns_true_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($user->isVerifikator());
    }

    public function test_is_verifikator_returns_true_for_verifikator(): void
    {
        $user = User::factory()->create(['role' => 'verifikator']);
        $this->assertTrue($user->isVerifikator());
    }

    public function test_is_verifikator_returns_false_for_petugas(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);
        $this->assertFalse($user->isVerifikator());
    }

    public function test_is_petugas_returns_true_for_petugas(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);
        $this->assertTrue($user->isPetugas());
    }

    public function test_is_petugas_returns_false_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertFalse($user->isPetugas());
    }

    public function test_can_verify_returns_true_for_verifikator_roles(): void
    {
        $user = User::factory()->create(['role' => 'verifikator']);
        $this->assertTrue($user->canVerify());
    }

    public function test_can_verify_returns_false_for_petugas(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);
        $this->assertFalse($user->canVerify());
    }

    public function test_email_is_unique(): void
    {
        User::factory()->create(['role' => 'admin', 'email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['role' => 'admin', 'email' => 'test@example.com']);
    }

    public function test_verifikasi_laporans_relation(): void
    {
        $user = User::factory()->create(['role' => 'verifikator']);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $user->verifikasiLaporans()
        );
    }

    public function test_password_is_hidden_from_serialization(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => 'secret123',
        ]);

        $array = $user->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }
}
