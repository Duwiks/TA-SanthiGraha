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
        Schema::table('nota_merah', function (Blueprint $table) {
            $table->enum('payment_stage', ['uang_muka', 'proses', 'selesai'])
                  ->default('proses')
                  ->after('amount');

            $table->unsignedInteger('payment_group_id')
                  ->nullable()
                  ->after('payment_stage');

            $table->foreign('payment_group_id')
                  ->references('id')
                  ->on('payment_groups')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_merah', function (Blueprint $table) {
            $table->dropForeign(['payment_group_id']);
            $table->dropColumn(['payment_stage', 'payment_group_id']);
        });
    }
};
