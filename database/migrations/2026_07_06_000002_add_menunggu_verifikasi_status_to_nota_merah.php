<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrasi data lama: status 'disetujui' → 'menunggu_konfirmasi' agar tidak truncate
        DB::statement("UPDATE nota_merah SET status = 'menunggu_konfirmasi' WHERE status = 'disetujui'");

        // Tambah status menunggu_verifikasi ke enum
        DB::statement("ALTER TABLE nota_merah MODIFY COLUMN status ENUM(
            'menunggu_persetujuan',
            'ditolak',
            'menunggu_konfirmasi',
            'menunggu_verifikasi',
            'selesai'
        ) NOT NULL DEFAULT 'menunggu_persetujuan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE nota_merah MODIFY COLUMN status ENUM(
            'menunggu_persetujuan',
            'disetujui',
            'ditolak',
            'menunggu_konfirmasi',
            'selesai'
        ) NOT NULL DEFAULT 'menunggu_persetujuan'");
    }
};
