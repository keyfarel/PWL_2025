<?php

namespace App\Http\Controllers;

use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\BarangModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StokController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object)[
            'title' => 'Daftar Stok',
            'list'  => ['Home', 'Stok']
        ];

        $page = (object)[
            'title' => 'Daftar stok dalam sistem'
        ];

        $activeMenu = 'stok';

        $barang = BarangModel::all();

        return view('stok.index', compact('breadcrumbs', 'page', 'activeMenu', 'barang'));
    }

    public function list(Request $request)
    {
        $stoks = StokModel::with(['supplier', 'user', 'barang'])->select('t_stok.*');

        if ($request->barang_id) {
            $stoks->where('barang_id', $request->barang_id);
        }

        return DataTables::of($stoks)
            ->addIndexColumn()
            ->editColumn('stok_tanggal', function ($stok) {
                return \Carbon\Carbon::parse($stok->stok_tanggal)->format('Y-m-d');
            })
            ->addColumn('supplier_nama', function ($stok) {
                return $stok->supplier ? $stok->supplier->supplier_nama : '-';
            })
            ->addColumn('user_nama', function ($stok) {
                return $stok->user ? $stok->user->nama : '-';
            })
            ->addColumn('barang_nama', function ($stok) {
                return $stok->barang ? $stok->barang->barang_nama : '-';
            })
            ->addColumn('aksi', function ($stok) {
                $btn  = '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button>';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }


    public function create_ajax()
    {
        $suppliers = SupplierModel::all();
        $users = UserModel::all();
        $barangs = BarangModel::all();
        return view('stok.create_ajax', compact('suppliers', 'users', 'barangs'));
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'supplier_id'  => 'required|exists:m_supplier,supplier_id',
                'user_id'      => 'required|exists:m_user,user_id',
                'barang_id'    => 'required|exists:m_barang,barang_id',
                'stok_tanggal' => 'required|date',
                'stok_jumlah'  => 'required|integer'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status'   => false,
                    'message'  => 'Validasi gagal.',
                    'msgField' => $validator->errors()
                ]);
            }

            StokModel::create([
                'supplier_id'  => $request->supplier_id,
                'user_id'      => $request->user_id,
                'barang_id'    => $request->barang_id,
                'stok_tanggal' => $request->stok_tanggal,
                'stok_jumlah'  => $request->stok_jumlah
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data stok berhasil disimpan.'
            ]);
        }
        return redirect('/');
    }

    public function edit_ajax($id)
    {
        $stok = StokModel::find($id);
        if (!$stok) {
            return view('stok.edit_ajax')->with('error', 'Data stok tidak ditemukan');
        }
        $suppliers = SupplierModel::all();
        $users     = UserModel::all();
        $barangs   = BarangModel::all();

        return view('stok.edit_ajax', compact('stok', 'suppliers', 'users', 'barangs'));
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'supplier_id'  => 'required|exists:m_supplier,supplier_id',
                'user_id'      => 'required|exists:m_user,user_id',
                'barang_id'    => 'required|exists:m_barang,barang_id',
                'stok_tanggal' => 'required|date',
                'stok_jumlah'  => 'required|integer'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status'   => false,
                    'message'  => 'Validasi gagal.',
                    'msgField' => $validator->errors()
                ]);
            }

            $stok = StokModel::find($id);
            if ($stok) {
                $stok->update([
                    'supplier_id'  => $request->supplier_id,
                    'user_id'      => $request->user_id,
                    'barang_id'    => $request->barang_id,
                    'stok_tanggal' => $request->stok_tanggal,
                    'stok_jumlah'  => $request->stok_jumlah
                ]);

                return response()->json([
                    'status'  => true,
                    'message' => 'Data stok berhasil diperbarui.'
                ]);
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data stok tidak ditemukan.'
                ]);
            }
        }
        return redirect('/');
    }

    public function show_ajax(string $id)
    {
        $stok = StokModel::with('supplier', 'user', 'barang')->find($id);

        return view('stok.show_ajax', compact('stok'));
    }

    public function confirm_ajax($id)
    {
        $stok = StokModel::find($id);
        return view('stok.confirm_ajax', compact('stok'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $stok = StokModel::find($id);
            if ($stok) {
                try {
                    $stok->delete();
                    return response()->json([
                        'status'  => true,
                        'message' => 'Data stok berhasil dihapus.'
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Data stok gagal dihapus karena masih terkait dengan data lain.'
                    ]);
                }
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data stok tidak ditemukan.'
                ]);
            }
        }
        return redirect('/');
    }
}
