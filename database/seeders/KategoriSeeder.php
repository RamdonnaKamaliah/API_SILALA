<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Novel',
            'Komik',
            'Teknologi',
            'Pendidikan',
            'Agama',
            'Sejarah',
        ];

        foreach ($data as $item) {
            Kategori::create([
                'nama_kategori' => $item
            ]);
        }
    }
}