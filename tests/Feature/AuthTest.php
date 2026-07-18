<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // Halaman dapat diakses
    // -------------------------------------------------------

    /** @test */
    public function halaman_login_dapat_diakses(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    /** @test */
    public function halaman_registrasi_dapat_diakses(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------
    // Registrasi
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_registrasi_dengan_data_valid(): void
    {
        $response = $this->post(route('register'), [
            'name'     => 'Budi Santoso',
            'username' => 'budi_santoso',
            'password' => 'password123',
            'phone'    => '081234567890',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'username' => 'budi_santoso',
            'role'     => 'pegawai',
        ]);
    }

    /** @test */
    public function registrasi_gagal_jika_username_sudah_digunakan(): void
    {
        User::factory()->create(['username' => 'sudah_ada']);

        $response = $this->post(route('register'), [
            'name'     => 'Test User',
            'username' => 'sudah_ada',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
    }

    /** @test */
    public function registrasi_gagal_jika_password_kurang_dari_6_karakter(): void
    {
        $response = $this->post(route('register'), [
            'name'     => 'Test User',
            'username' => 'testuser99',
            'password' => '123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function registrasi_gagal_jika_nama_kosong(): void
    {
        $response = $this->post(route('register'), [
            'name'     => '',
            'username' => 'testuser100',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------
    // Login
    // -------------------------------------------------------

    /** @test */
    public function login_berhasil_dengan_kredensial_yang_valid(): void
    {
        $user = User::factory()->create([
            'username' => 'testlogin',
            'password' => 'password123', // UserFactory definition does not hash unless defined or handled by Model mutator/cast
        ]);

        $response = $this->post(route('login'), [
            'username' => 'testlogin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function login_gagal_dengan_password_salah(): void
    {
        User::factory()->create([
            'username' => 'testlogin2',
            'password' => 'password123',
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'username' => 'testlogin2',
            'password' => 'wrongpassword',
        ]);

        // Login gagal → dikembalikan ke login page (back())
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    // -------------------------------------------------------
    // Logout
    // -------------------------------------------------------

    /** @test */
    public function logout_berhasil_dan_redirect_ke_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
