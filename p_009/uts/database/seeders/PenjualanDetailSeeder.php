<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanDetailSeeder extends Seeder
{
    public function run(): void
    {
        $penjualanIds = DB::table('t_penjualan')->pluck('penjualan_id')->toArray();
        $barangIds = DB::table('m_barang')->pluck('barang_id')->toArray();

        if (empty($penjualanIds) || empty($barangIds)) {
            throw new \Exception('Seeder gagal: Tidak ada data di t_penjualan atau m_barang.');
        }

        $data = [];
        foreach ($penjualanIds as $penjualan_id) {
            for ($j = 1; $j <= 3; $j++) { // 3 barang per transaksi
                $barang_id = $barangIds[array_rand($barangIds)];
                $harga = DB::table('m_barang')->where('barang_id', $barang_id)->value('harga_jual');
                $jumlah = rand(1, 5);

                $data[] = [
                    'penjualan_id' => $penjualan_id,
                    'barang_id' => $barang_id,
                    'harga' => $harga,
                    'jumlah' => $jumlah,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Update total harga transaksi
                DB::table('t_penjualan')->where('penjualan_id', $penjualan_id)->increment('total_harga', $jumlah * $harga);
            }
        }

        DB::table('t_penjualan_detail')->insert($data);
    }
}
