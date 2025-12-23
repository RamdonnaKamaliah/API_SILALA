<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataBuku;
use Faker\Factory as Faker;

class DataBukuSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        for ($i = 0; $i < 10; $i++) {
            DataBuku::create([
                'judul_buku'      => $faker->sentence(3),
                'penulis'         => $faker->name,
                'penerbit'        => $faker->company,
                'tahun_terbit'    => $faker->year,
                'bahasa'          => $faker->randomElement(['Indonesia', 'Inggris']),
                'jumlah_halaman'  => $faker->numberBetween(100, 500),
                'edisi'           => 'Edisi ' . $faker->numberBetween(1, 5),
                'deskripsi'       => $faker->paragraph(3),
                'stok'            => $faker->numberBetween(1, 30),
                'foto_buku'       => 'dummy.jpg',
                'file_buku'       => 'dummy.pdf',
                'stok'          => '12',
                
            ]);
        }
    }
}