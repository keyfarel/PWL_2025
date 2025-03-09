<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object)[
            'title' => 'Daftar Barang',
            'list'  => ['Home', 'Barang']
        ];

        $page = (object)[
            'title' => 'Daftar barang dalam sistem'
        ];

        $activeMenu = 'barang';

        return view('barang.index', compact('breadcrumbs', 'page', 'activeMenu'));
    }

    public function list(Request $request)
    {
        $barangs = BarangModel::with('kategori')->select('m_barang.*');

        return DataTables::of($barangs)
            ->addIndexColumn()
            ->addColumn('kategori', function ($barang) {
                return $barang->kategori ? $barang->kategori->kategori_nama : '-';
            })
            ->addColumn('aksi', function ($barang) {
                $btn = '<a href="' . url('/barang/' . $barang->barang_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                $btn .= '<a href="' . url('/barang/' . $barang->barang_id . '/edit') . '" class="btn btn-warning btn-sm">Edit</a> ';
                $btn .= '<form class="d-inline-block" method="POST" action="' . url('/barang/' . $barang->barang_id) . '" style="display:inline;">'
                    . csrf_field() . method_field('DELETE') .
                    '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin menghapus data ini?\');">Hapus</button>
            </form>';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show($id)
    {
        $barang = BarangModel::with('kategori')->find($id);

        $breadcrumbs = (object)[
            'title' => 'Detail Barang',
            'list'  => ['Home', 'Barang', 'Detail']
        ];

        $page = (object)[
            'title' => 'Detail barang'
        ];

        $activeMenu = 'barang';

        return view('barang.show', compact('barang', 'breadcrumbs', 'page', 'activeMenu'));
    }

    public function create()
    {
        $breadcrumbs = (object)[
            'title' => 'Tambah Barang',
            'list'  => ['Home', 'Barang', 'Tambah']
        ];

        $page = (object)[
            'title' => 'Tambah barang baru'
        ];

        $activeMenu = 'barang';

        // Mengambil semua data kategori untuk pilihan dropdown
        $kategori = KategoriModel::all();

        return view('barang.create', compact('breadcrumbs', 'page', 'activeMenu', 'kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id'   => 'required|integer',
            'barang_kode'   => 'required|string|max:50|unique:m_barang,barang_kode',
            'barang_nama'   => 'required|string|max:100',
            'harga_beli'    => 'required|numeric',
            'harga_jual'    => 'required|numeric'
        ]);

        BarangModel::create([
            'kategori_id'   => $request->kategori_id,
            'barang_kode'   => $request->barang_kode,
            'barang_nama'   => $request->barang_nama,
            'harga_beli'    => $request->harga_beli,
            'harga_jual'    => $request->harga_jual
        ]);

        return redirect('/barang')->with('success', 'Data barang berhasil ditambahkan');
    }

    public function edit($id)
    {
        $breadcrumbs = (object)[
            'title' => 'Edit Barang',
            'list'  => ['Home', 'Barang', 'Edit']
        ];

        $page = (object)[
            'title' => 'Edit barang'
        ];

        $activeMenu = 'barang';

        $barang = BarangModel::findOrFail($id);
        $kategori = KategoriModel::all();

        return view('barang.edit', compact('breadcrumbs', 'page', 'activeMenu', 'barang', 'kategori'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori_id'   => 'required|integer',
            'barang_kode'   => 'required|string|max:50|unique:m_barang,barang_kode,' . $id . ',barang_id',
            'barang_nama'   => 'required|string|max:100',
            'harga_beli'    => 'required|numeric',
            'harga_jual'    => 'required|numeric'
        ]);

        $barang = BarangModel::findOrFail($id);
        $barang->update([
            'kategori_id'   => $request->kategori_id,
            'barang_kode'   => $request->barang_kode,
            'barang_nama'   => $request->barang_nama,
            'harga_beli'    => $request->harga_beli,
            'harga_jual'    => $request->harga_jual
        ]);

        return redirect('/barang')->with('success', 'Data barang berhasil diperbarui');
    }

    public function destroy($id)
    {
        $barang = BarangModel::find($id);

        if (!$barang) {
            return redirect('/barang')->with('error', 'Data barang tidak ditemukan');
        }

        try {
            $barang->delete();
            return redirect('/barang')->with('success', 'Data barang berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/barang')->with('error', 'Data barang gagal dihapus karena masih terkait dengan data lain');
        }
    }
}
