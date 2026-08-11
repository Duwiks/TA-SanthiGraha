<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_groups', function (Blueprint $table) {
            $table->enum('type', ['pemasukan', 'pengeluaran'])->default('pengeluaran')->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_groups', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
