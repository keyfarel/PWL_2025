<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierModel;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object) [
            'title' => 'Daftar Supplier',
            'list' => ['Home', 'Supplier'],
        ];

        $page = (object) [
            'title' => 'Daftar supplier yang terdaftar dalam sistem',
        ];

        $activeMenu = 'supplier';

        return view('supplier.index', [
            'breadcrumbs' => $breadcrumbs,
            'page' => $page,
            'activeMenu' => $activeMenu,
        ]);
    }

    public function list(Request $request)
    {
        $supplier = SupplierModel::select('supplier_id', 'supplier_kode', 'supplier_nama', 'supplier_alamat');

        return DataTables::of($supplier)
            ->addIndexColumn()
            ->addColumn('aksi', function ($item) {
                $btn  = '<button onclick="modalAction(\'' . url('/supplier/' . $item->supplier_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/supplier/' . $item->supplier_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/supplier/' . $item->supplier_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button>';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        return view('supplier.create_ajax');
    }

    public function store_ajax(Request $request)
    {
        $rules = [
            'supplier_kode'   => 'required|string|max:10|unique:m_supplier,supplier_kode',
            'supplier_nama'   => 'required|string|max:100',
            'supplier_alamat' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi Gagal',
                'msgField' => $validator->errors(),
            ]);
        }

        SupplierModel::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Data supplier berhasil disimpan',
        ]);
    }

    public function show_ajax(string $id)
    {
        $supplier = SupplierModel::find($id);

        return view('supplier.show_ajax', compact('supplier'));
    }

    public function edit_ajax(string $id)
    {
        $supplier = SupplierModel::find($id);

        return view('supplier.edit_ajax', compact('supplier'));
    }

    public function update_ajax(Request $request, $id)
    {
        $rules = [
            'supplier_kode'   => 'required|max:10|unique:m_supplier,supplier_kode,' . $id . ',supplier_id',
            'supplier_nama'   => 'required|max:100',
            'supplier_alamat' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'msgField' => $validator->errors(),
            ]);
        }

        $check = SupplierModel::find($id);
        if ($check) {
            $check->update($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data tidak ditemukan',
        ]);
    }

    public function confirm_ajax(string $id)
    {
        $supplier = SupplierModel::find($id);

        return view('supplier.confirm_ajax', compact('supplier'));
    }

    public function delete_ajax(Request $request, $id)
    {
        $supplier = SupplierModel::find($id);
        if ($supplier) {
            try {
                $supplier->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Data supplier berhasil dihapus.',
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data supplier gagal dihapus karena masih terkait dengan data lain.',
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Data tidak ditemukan',
        ]);
    }
}
