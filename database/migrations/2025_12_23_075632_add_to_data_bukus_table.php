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
        Schema::table('data_bukus', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'arsip'])->default('aktif')->after('file_buku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_bukus', function (Blueprint $table) {
            //
        });
    }
};