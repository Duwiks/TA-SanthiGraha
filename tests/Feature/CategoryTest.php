<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\NotaMerah;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin   = User::factory()->admin()->create();
        $this->pegawai = User::factory()->pegawai()->create();
    }

    // -------------------------------------------------------
    // Index
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_melihat_daftar_kategori(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('categories.index'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------
    // Store (Create)
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_membuat_kategori_baru(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('categories.store'), [
                'category_name' => 'Transportasi',
                'description'   => 'Biaya transportasi lapangan',
            ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['category_name' => 'Transportasi']);
    }

    /** @test */
    public function pembuatan_kategori_gagal_jika_nama_duplikat(): void
    {
        Category::factory()->create(['category_name' => 'Transportasi']);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('categories.store'), [
                'category_name' => 'Transportasi',
            ]);

        $response->assertSessionHasErrors('category_name');
    }

    /** @test */
    public function pembuatan_kategori_gagal_jika_nama_mengandung_karakter_khusus(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('categories.store'), [
                'category_name' => 'Kategori@#!',
            ]);

        $response->assertSessionHasErrors('category_name');
    }

    // -------------------------------------------------------
    // Update
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_mengupdate_kategori(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('categories.update', $category->id), [
                'category_name' => 'Nama Kategori Baru',
                'description'   => 'Deskripsi baru',
            ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['category_name' => 'Nama Kategori Baru']);
    }

    // -------------------------------------------------------
    // Destroy (Delete)
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_menghapus_kategori_yang_tidak_digunakan(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('categories.destroy', $category->id));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function admin_tidak_dapat_menghapus_kategori_yang_digunakan_transaksi(): void
    {
        $category = Category::factory()->create();
        Transaction::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('categories.destroy', $category->id));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function admin_tidak_dapat_menghapus_kategori_yang_digunakan_nota_merah(): void
    {
        $category = Category::factory()->create();
        NotaMerah::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('categories.destroy', $category->id));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
