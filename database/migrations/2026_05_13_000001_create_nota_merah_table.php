<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nota_merah', function (Blueprint $table) {
            $table->increments('id');

            // Pegawai yang mengajukan
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Proyek & Kategori
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            // Detail Pengajuan
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['Cash', 'Bank BPD', 'BRI', 'BCA']);

            // Foto bukti kebutuhan (nota merah / RAB / kwitansi sementara)
            $table->string('nota_photo', 255)->nullable();

            // Foto bukti realisasi (setelah belanja dilakukan)
            $table->string('realisasi_photo', 255)->nullable();

            // Tanggal realisasi belanja (diisi pegawai saat upload bukti realisasi)
            $table->date('realisasi_date')->nullable();

            // Status 5 tahap
            $table->enum('status', [
                'menunggu_persetujuan',
                'disetujui',
                'ditolak',
                'menunggu_konfirmasi',
                'selesai',
            ])->default('menunggu_persetujuan');

            // Alasan penolakan oleh admin
            $table->text('rejection_reason')->nullable();

            // Admin yang menyetujui / konfirmasi
            $table->unsignedInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            // Timestamp konfirmasi akhir
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_merah');
    }
};
