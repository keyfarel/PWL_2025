<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KategoriController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object) [
            'title' => 'Daftar Kategori',
            'list' => ['Home', 'Kategori'],
        ];

        $page = (object) [
            'title' => 'Daftar kategori dalam sistem',
        ];

        $activeMenu = 'kategori';

        $kategories = KategoriModel::all();

        return view('kategori.index', compact('breadcrumbs', 'page', 'activeMenu', 'kategories'));
    }

    public function list(Request $request)
    {
        $kategories = KategoriModel::select('kategori_id', 'kategori_kode', 'kategori_nama');

        if ($request->kategori_id) {
            $kategories->where('kategori_id', $request->kategori_id);
        }

        return DataTables::of($kategories)
            ->addIndexColumn()
            ->addColumn('aksi', function ($kategori) {

                $btn = '<button onclick="modalAction(\'' . url('/kategori/' . $kategori->kategori_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/kategori/' . $kategori->kategori_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button>';

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        return view('kategori.create_ajax');
    }

    // Menyimpan data kategori via AJAX
    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_kode' => 'required|string|max:50|unique:m_kategori,kategori_kode',
                'kategori_nama' => 'required|string|max:100|unique:m_kategori,kategori_nama',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(),
                ]);
            }

            KategoriModel::create([
                'kategori_kode' => $request->kategori_kode,
                'kategori_nama' => $request->kategori_nama,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data kategori berhasil disimpan.',
            ]);
        }

        return redirect('/');
    }

    public function edit_ajax($id)
    {
        $kategori = KategoriModel::find($id);

        return view('kategori.edit_ajax', compact('kategori'));
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_kode' => 'required|string|max:50|unique:m_kategori,kategori_kode,' . $id . ',kategori_id',
                'kategori_nama' => 'required|string|max:100|unique:m_kategori,kategori_nama,' . $id . ',kategori_id',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(),
                ]);
            }

            $kategori = KategoriModel::find($id);
            if ($kategori) {
                $kategori->update([
                    'kategori_kode' => $request->kategori_kode,
                    'kategori_nama' => $request->kategori_nama,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Data kategori berhasil diperbarui.',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data kategori tidak ditemukan.',
                ]);
            }
        }

        return redirect('/');
    }

    public function confirm_ajax($id)
    {
        $kategori = KategoriModel::find($id);

        return view('kategori.confirm_ajax', compact('kategori'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $kategori = KategoriModel::find($id);
            if ($kategori) {
                try {
                    $kategori->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data kategori berhasil dihapus.',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data kategori gagal dihapus karena masih terkait dengan data lain.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data kategori tidak ditemukan.',
                ]);
            }
        }

        return redirect('/');
    }

    public function import()
    {
        return view('kategori.import');
    }

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            // Validasi file: harus .xlsx, max 2MB
            $rules = [
                'file_kategori' => ['required', 'mimes:xlsx', 'max:2048'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status'   => false,
                    'message'  => 'Validasi Gagal.' . "\n" . 'Mohon ikuti instruksi di template.',
                    'msgField' => $validator->errors(),
                ]);
            }

            try {
                // Load file Excel
                $file = $request->file('file_kategori');
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray(null, true, true, true);

                // Pastikan ada minimal 2 baris (1 header + minimal 1 data)
                if (count($data) <= 1) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Tidak ada data yang diimport.' . "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }

                // 1) Pastikan header sesuai
                // Baris pertama = index 1 (A, B, ...)
                $headerKode = strtolower(trim($data[1]['A'] ?? ''));
                $headerNama = strtolower(trim($data[1]['B'] ?? ''));

                if ($headerKode !== 'kategori_kode' || $headerNama !== 'kategori_nama') {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Header file Excel tidak sesuai. ' .
                            'Kolom A harus "kategori_kode" dan kolom B harus "kategori_nama".' .
                            "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }

                $insert = [];

                // 2) Looping mulai dari baris kedua (baris pertama = header)
                foreach ($data as $rowIndex => $rowValue) {
                    if ($rowIndex == 1) {
                        continue; // skip header
                    }

                    $kategoriKode = trim($rowValue['A'] ?? '');
                    $kategoriNama = trim($rowValue['B'] ?? '');

                    // Cek kolom kosong
                    if ($kategoriKode === '' || $kategoriNama === '') {
                        // Bisa di-skip atau return error; di sini kita skip
                        continue;
                    }

                    // 3) Cek apakah data sudah ada di DB
                    $existing = KategoriModel::where('kategori_kode', $kategoriKode)
                        ->orWhere('kategori_nama', $kategoriNama)
                        ->first();

                    if ($existing) {
                        // Berhenti dan beri tahu user data duplikat
                        return response()->json([
                            'status'  => false,
                            'message' => "Kategori dengan kode '{$kategoriKode}' atau nama '{$kategoriNama}' sudah ada." .
                                "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    // Data lolos pengecekan, siap di-insert
                    $insert[] = [
                        'kategori_kode' => $kategoriKode,
                        'kategori_nama' => $kategoriNama,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }

                // 4) Proses insert
                if (count($insert) > 0) {
                    // Insert data ke tabel, data duplikat tidak mungkin karena sudah dicek di atas
                    KategoriModel::insert($insert);

                    return response()->json([
                        'status'  => true,
                        'message' => 'Data berhasil diimport',
                    ]);
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Tidak ada data valid yang diimport.' . "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage() .
                        "\n" . 'Mohon ikuti instruksi di template.',
                ]);
            }
        }

        return redirect('/');
    }
}
