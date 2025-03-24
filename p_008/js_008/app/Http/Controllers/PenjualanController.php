<?php

namespace App\Http\Controllers;

use App\Models\PenjualanModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PenjualanController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object) [
            'title' => 'Daftar Penjualan',
            'list' => ['Home', 'Penjualan'],
        ];

        $page = (object) [
            'title' => 'Daftar penjualan',
        ];

        $activeMenu = 'penjualan';

        return view('penjualan.index', compact('breadcrumbs', 'page', 'activeMenu'));
    }

    public function list(Request $request)
    {
        $penjualan = PenjualanModel::select('penjualan_id', 'penjualan_kode', 'total_harga', 'pembeli', 'penjualan_tanggal', 'user_id')
            ->with('user');

        return DataTables::of($penjualan)
            ->addIndexColumn()
            ->addColumn('user_name', function ($p) {
                return $p->user ? $p->user->nama : '-';
            })
            ->addColumn('aksi', function ($p) {
                $btn = '<button onclick="modalAction(\''.url('/penjualan/'.$p->penjualan_id.'/show_ajax').'\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/penjualan/'.$p->penjualan_id.'/delete_ajax').'\')" class="btn btn-danger btn-sm">Hapus</button> ';

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show_ajax($id)
    {
        // Memuat data penjualan beserta relasi user dan detail (termasuk barang)
        $penjualan = PenjualanModel::with(['user', 'detail.barang'])->find($id);

        // Mengembalikan view show_ajax (misalnya resources/views/penjualan/show_ajax.blade.php)
        return view('penjualan.detail.index', compact('penjualan'));
    }

    public function confirm_ajax($id)
    {
        // Cari data penjualan berdasarkan id
        $penjualan = PenjualanModel::find($id);

        // Kembalikan view konfirmasi hapus via AJAX
        return view('penjualan.confirm_ajax', compact('penjualan'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $penjualan = PenjualanModel::find($id);
            if ($penjualan) {
                try {
                    $penjualan->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data penjualan berhasil dihapus.',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data penjualan gagal dihapus karena masih terkait dengan data lain.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data penjualan tidak ditemukan.',
                ]);
            }
        }

        return redirect('/');
    }
}
