<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membersihkan kolom-kolom lama & memastikan kolom yang diperlukan ada.
     */
    public function up(): void
    {
        Schema::table('nota_merah', function (Blueprint $table) {
            // -------------------------------------------------------
            // Hapus kolom-kolom lama yang tidak diperlukan lagi
            // -------------------------------------------------------
            $columnsToDrop = [];

            $existing = DB::select('SHOW COLUMNS FROM nota_merah');
            $existingFields = array_column($existing, 'Field');

            // Hapus payment_method jika masih ada
            if (in_array('payment_method', $existingFields)) {
                $columnsToDrop[] = 'payment_method';
            }
            // Hapus bank_toko (duplikat/lama)
            if (in_array('bank_toko', $existingFields)) {
                $columnsToDrop[] = 'bank_toko';
            }
            // Hapus no_rekening_tujuan (duplikat/lama)
            if (in_array('no_rekening_tujuan', $existingFields)) {
                $columnsToDrop[] = 'no_rekening_tujuan';
            }
            // Hapus bukti_transfer (lama — diganti transfer_proof)
            if (in_array('bukti_transfer', $existingFields)) {
                $columnsToDrop[] = 'bukti_transfer';
            }
            // Hapus transferred_at (lama)
            if (in_array('transferred_at', $existingFields)) {
                $columnsToDrop[] = 'transferred_at';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // -------------------------------------------------------
        // Tambah kolom baru jika belum ada
        // -------------------------------------------------------
        Schema::table('nota_merah', function (Blueprint $table) {
            $existing = DB::select('SHOW COLUMNS FROM nota_merah');
            $existingFields = array_column($existing, 'Field');

            if (!in_array('bank_tujuan', $existingFields)) {
                $table->string('bank_tujuan', 100)->nullable()->after('amount');
            }
            if (!in_array('no_rekening', $existingFields)) {
                $table->string('no_rekening', 50)->nullable()->after('bank_tujuan');
            }
            if (!in_array('nama_pemilik_rekening', $existingFields)) {
                $table->string('nama_pemilik_rekening', 150)->nullable()->after('no_rekening');
            }
            if (!in_array('transfer_proof', $existingFields)) {
                $table->string('transfer_proof', 255)->nullable()->after('realisasi_date');
            }
        });

        // -------------------------------------------------------
        // Bersihkan status enum — pastikan hanya status yang diperlukan
        // -------------------------------------------------------
        DB::statement("ALTER TABLE nota_merah MODIFY COLUMN status ENUM(
            'menunggu_persetujuan',
            'disetujui',
            'ditolak',
            'menunggu_konfirmasi',
            'selesai'
        ) NOT NULL DEFAULT 'menunggu_persetujuan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_merah', function (Blueprint $table) {
            $existing = DB::select('SHOW COLUMNS FROM nota_merah');
            $existingFields = array_column($existing, 'Field');

            $toDrop = [];
            foreach (['transfer_proof'] as $col) {
                if (in_array($col, $existingFields)) {
                    $toDrop[] = $col;
                }
            }
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }

            if (!in_array('payment_method', $existingFields)) {
                $table->enum('payment_method', ['Cash', 'Bank BPD', 'BRI', 'BCA'])
                    ->default('Cash')
                    ->after('amount');
            }
        });
    }
};
