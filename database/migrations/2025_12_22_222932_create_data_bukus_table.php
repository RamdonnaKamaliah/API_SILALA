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
        Schema::create('data_bukus', function (Blueprint $table) {

            $table->id();
            $table->string('foto_buku')->nullable();
            $table->string('judul_buku');
            $table->string('penulis');
            $table->string('penerbit');
            $table->year('tahun_terbit');
            $table->string('bahasa');
            $table->string('kategori');
            $table->integer('jumlah_halaman');
            $table->string('edisi');
            $table->text('deskripsi');
            $table->integer('stok')->default(0);
            $table->string('file_buku')->nullable();
            $table->unsignedBigInteger('foto_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_bukus');
    }
};