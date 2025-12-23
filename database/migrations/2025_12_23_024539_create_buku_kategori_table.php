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
       Schema::create('buku_kategori', function (Blueprint $table) {
    $table->id();

    $table->foreignId('data_buku_id')
          ->constrained('data_bukus')
          ->cascadeOnDelete();

    $table->foreignId('data_kategori_id')
          ->constrained('data_kategoris')
          ->cascadeOnDelete();

    $table->timestamps();

    $table->unique(['data_buku_id', 'data_kategori_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_kategori');
    }
};