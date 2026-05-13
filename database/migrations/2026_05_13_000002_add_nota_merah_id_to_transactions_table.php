<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom nota_merah_id ke tabel transactions untuk traceability.
     * Jika sebuah transaksi berasal dari konfirmasi nota merah, kolom ini akan terisi.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('nota_merah_id')->nullable()->after('approved_by');
            $table->foreign('nota_merah_id')->references('id')->on('nota_merah')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['nota_merah_id']);
            $table->dropColumn('nota_merah_id');
        });
    }
};
