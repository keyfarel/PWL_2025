<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'kategori_kode' => 'ELK', 'kategori_nama' => 'Elektronik'],
            ['id' => 2, 'kategori_kode' => 'PAK', 'kategori_nama' => 'Pakaian'],
            ['id' => 3, 'kategori_kode' => 'MAK', 'kategori_nama' => 'Makanan'],
            ['id' => 4, 'kategori_kode' => 'MIN', 'kategori_nama' => 'Minuman'],
            ['id' => 5, 'kategori_kode' => 'ALT', 'kategori_nama' => 'Alat Tulis'],
        ];

        DB::table('m_kategori')->insert($data);
    }
}
