<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $data = [];
        $barang = [
            ['Laptop', 'Elektronik'],
            ['Smartphone', 'Elektronik'],
            ['T-Shirt', 'Pakaian'],
            ['Celana Jeans', 'Pakaian'],
            ['Snack', 'Makanan'],
            ['Susu', 'Minuman'],
            ['Pensil', 'Alat Tulis'],
            ['Pulpen', 'Alat Tulis'],
            ['Kopi', 'Minuman'],
            ['Teh', 'Minuman'],
            ['Roti', 'Makanan'],
            ['Monitor', 'Elektronik'],
            ['Keyboard', 'Elektronik'],
            ['Mouse', 'Elektronik'],
            ['Tas', 'Pakaian'],
        ];

        foreach ($barang as $index => $b) {
            $data[] = [
                'barang_id' => $index + 1,
                'kategori_id' => ($index % 5) + 1,
                'barang_kode' => 'BRG'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'barang_nama' => $b[0],
                'harga_beli' => rand(5000, 50000),
                'harga_jual' => rand(51000, 100000),
            ];
        }

        DB::table('m_barang')->insert($data);
    }
}
