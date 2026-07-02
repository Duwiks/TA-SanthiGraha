<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom status ke tabel projects.
     * 'aktif'  = project sedang berjalan (default)
     * 'selesai' = admin sudah menandai project selesai (terkunci)
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'selesai'])
                  ->default('aktif')
                  ->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
