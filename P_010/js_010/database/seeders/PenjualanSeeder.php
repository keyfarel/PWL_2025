<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PenjualanSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('m_user')->pluck('user_id')->toArray();

        if (empty($userIds)) {
            throw new \Exception('Seeder gagal: Tidak ada user di tabel m_user.');
        }

        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'penjualan_id' => $i,
                'user_id' => $userIds[array_rand($userIds)], // Ambil user yang ada
                'pembeli' => 'Pembeli '.$i,
                'penjualan_kode' => strtoupper(Str::random(10)), // Kode unik
                'penjualan_tanggal' => now()->subDays(rand(1, 30)), // Tanggal acak dalam 30 hari terakhir
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('t_penjualan')->insert($data);
    }
}
