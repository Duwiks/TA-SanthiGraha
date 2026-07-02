<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mengubah constraint foreign key project_id dan category_id
     * dari onDelete('cascade') menjadi onDelete('restrict'),
     * sehingga penghapusan Project/Category yang masih digunakan
     * akan diblokir di level database.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop foreign key lama
            $table->dropForeign(['project_id']);
            $table->dropForeign(['category_id']);

            // Buat ulang foreign key dengan onDelete('restrict')
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('restrict');

            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Kembalikan ke cascade jika rollback
            $table->dropForeign(['project_id']);
            $table->dropForeign(['category_id']);

            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');

            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');
        });
    }
};
