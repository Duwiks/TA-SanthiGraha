<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Nullable → transaksi lama sebelum fitur ini tetap valid tanpa migrasi data
            $table->unsignedInteger('payment_group_id')->nullable()->after('nota_merah_id');
            $table->foreign('payment_group_id')
                  ->references('id')->on('payment_groups')
                  ->nullOnDelete(); // Jika Payment Group dihapus, kolom cukup di-null-kan

            // Hanya relevan untuk tipe pengeluaran; pemasukan dibiarkan null
            $table->enum('payment_stage', ['uang_muka', 'proses', 'selesai'])->nullable()->after('payment_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_group_id']);
            $table->dropColumn(['payment_group_id', 'payment_stage']);
        });
    }
};
