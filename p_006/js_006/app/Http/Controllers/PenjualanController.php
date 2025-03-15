<?php

namespace App\Http\Controllers;

use App\Models\PenjualanModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PenjualanController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object)[
            'title' => 'Daftar Penjualan',
            'list'  => ['Home', 'Penjualan']
        ];

        $page = (object)[
            'title' => 'Daftar penjualan'
        ];

        $activeMenu = 'penjualan';

        return view('penjualan.index', compact('breadcrumbs', 'page', 'activeMenu'));
    }

    public function list(Request $request)
    {
        $penjualans = PenjualanModel::with('user')->select('t_penjualan.*');

        return DataTables::of($penjualans)
            ->addIndexColumn()
            ->addColumn('user_name', function ($p) {
                return $p->user ? $p->user->nama : '-';
            })
            ->addColumn('aksi', function ($p) {
                $btn  = '<a href="' . url('/penjualan/' . $p->penjualan_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                $btn .= '<form class="d-inline-block" method="POST" action="' . url('/penjualan/' . $p->penjualan_id) . '" style="display:inline;">'
                    . csrf_field() . method_field('DELETE') .
                    '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin hapus data ini?\');">Hapus</button>
                     </form>';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show($id)
    {
        // Memuat data penjualan beserta relasi user dan detail (beserta barang)
        $penjualan = PenjualanModel::with(['user', 'detail.barang'])->find($id);

        $breadcrumbs = (object)[
            'title' => 'Detail Penjualan',
            'list'  => ['Home', 'Penjualan', 'Detail']
        ];

        $page = (object)[
            'title' => 'Detail penjualan'
        ];

        $activeMenu = 'penjualan';

        return view('penjualan.detail.index', compact('penjualan', 'breadcrumbs', 'page', 'activeMenu'));
    }

    public function destroy($id)
    {
        $penjualan = PenjualanModel::find($id);

        if (!$penjualan) {
            return redirect('/penjualan')->with('error', 'Data penjualan tidak ditemukan');
        }

        try {
            $penjualan->delete();
            return redirect('/penjualan')->with('success', 'Data penjualan berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/penjualan')->with('error', 'Data penjualan gagal dihapus karena masih terkait dengan data lain');
        }
    }
}
