<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\PenjualanDetailModel;
use App\Models\StokModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        // Data untuk breadcrumbs dan menu aktif
        $breadcrumb = (object) [
            'title' => 'Selamat Datang',
            'list' => ['Home', 'Welcome'],
        ];

        $activeMenu = 'dashboard';

        // Ambil filter tahun dan bulan dari request
        $tahun = $request->input('tahun', date('Y')); // Default tahun adalah tahun sekarang
        $bulan = $request->input('bulan', date('m')); // Default bulan adalah bulan sekarang

        // Ambil data stok masuk per barang dengan filter tahun dan bulan
        $stokMasuk = StokModel::select('barang_id', DB::raw('SUM(stok_jumlah) as total_masuk'))
            ->whereYear('stok_tanggal', $tahun)
            ->whereMonth('stok_tanggal', $bulan)
            ->groupBy('barang_id');

        // Ambil data stok terjual per barang dengan filter tahun dan bulan
        $stokTerjual = PenjualanDetailModel::select('barang_id', DB::raw('SUM(jumlah) as total_terjual'))
            ->join('t_penjualan as penjualan', 'penjualan.penjualan_id', '=', 't_penjualan_detail.penjualan_id')
            ->whereYear('penjualan.penjualan_tanggal', $tahun)
            ->whereMonth('penjualan.penjualan_tanggal', $bulan)
            ->groupBy('barang_id');

        // Gabungkan data ke dalam tabel barang
        $ringkasan = BarangModel::from('m_barang as barang')
            ->select(
                'barang.barang_id',
                'barang.barang_nama',
                DB::raw('COALESCE(masuk.total_masuk, 0) as total_masuk'),
                DB::raw('COALESCE(terjual.total_terjual, 0) as total_terjual'),
                DB::raw('COALESCE(masuk.total_masuk, 0) - COALESCE(terjual.total_terjual, 0) as stok_ready')
            )
            ->leftJoinSub($stokMasuk, 'masuk', function ($join) {
                $join->on('barang.barang_id', '=', 'masuk.barang_id');
            })
            ->leftJoinSub($stokTerjual, 'terjual', function ($join) {
                $join->on('barang.barang_id', '=', 'terjual.barang_id');
            })
            ->get();

        // Kategori ringkasan dengan filter tahun dan bulan
        $kategoriRingkasan = BarangModel::join('m_kategori as k', 'm_barang.kategori_id', '=', 'k.kategori_id')
            ->leftJoinSub($stokMasuk, 'masuk', function ($join) {
                $join->on('m_barang.barang_id', '=', 'masuk.barang_id');
            })
            ->leftJoinSub($stokTerjual, 'terjual', function ($join) {
                $join->on('m_barang.barang_id', '=', 'terjual.barang_id');
            })
            ->select(
                'k.kategori_nama',
                DB::raw('SUM(COALESCE(masuk.total_masuk, 0)) as total_masuk'),
                DB::raw('SUM(COALESCE(terjual.total_terjual, 0)) as total_terjual')
            )
            ->groupBy('k.kategori_nama')
            ->get();

        // Kirim ke view dengan nama yang konsisten
        return view('welcome', [
            'breadcrumbs' => $breadcrumb,
            'activeMenu' => $activeMenu,
            'ringkasan' => $ringkasan,
            'kategoriRingkasan' => $kategoriRingkasan,
            'tahun' => $tahun,
            'bulan' => $bulan
        ]);
    }
}
