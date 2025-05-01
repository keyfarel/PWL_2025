<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BarangModel;
use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Barryvdh\DomPDF\Facade\Pdf;

class StokController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object) [
            'title' => 'Daftar Stok',
            'list' => ['Home', 'Stok'],
        ];

        $page = (object) [
            'title' => 'Daftar stok dalam sistem',
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
                $btn = '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
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
                'supplier_id' => 'required|exists:m_supplier,supplier_id',
                'user_id' => 'required|exists:m_user,user_id',
                'barang_id' => 'required|exists:m_barang,barang_id',
                'stok_tanggal' => 'required|date',
                'stok_jumlah' => 'required|integer',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(),
                ]);
            }

            StokModel::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => $request->user_id,
                'barang_id' => $request->barang_id,
                'stok_tanggal' => $request->stok_tanggal,
                'stok_jumlah' => $request->stok_jumlah,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data stok berhasil disimpan.',
            ]);
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
                        'status' => true,
                        'message' => 'Data stok berhasil dihapus.',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data stok gagal dihapus karena masih terkait dengan data lain.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data stok tidak ditemukan.',
                ]);
            }
        }

        return redirect('/');
    }

    public function import()
    {
        return view('stok.import');
    }

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            // Validasi file: harus .xlsx dengan ukuran maksimal 2MB
            $rules = [
                'file_stok' => ['required', 'mimes:xlsx', 'max:2048'],
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
                // Ambil file Excel yang di-upload
                $file = $request->file('file_stok');
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray(null, true, true, true);

                // Pastikan ada data minimal (header + 1 baris data)
                if (count($data) <= 1) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Tidak ada data yang diimport.' . "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }

                // Validasi header file
                $headerA = strtolower(str_replace(' ', '_', trim($data[1]['A'] ?? '')));
                $headerB = strtolower(str_replace(' ', '_', trim($data[1]['B'] ?? '')));
                $headerC = strtolower(str_replace(' ', '_', trim($data[1]['C'] ?? '')));
                $headerD = strtolower(str_replace(' ', '_', trim($data[1]['D'] ?? '')));
                $expectedHeader = ['supplier_id', 'user_id', 'barang_id', 'stok_jumlah'];
                if (!(
                    $headerA === $expectedHeader[0] &&
                    $headerB === $expectedHeader[1] &&
                    $headerC === $expectedHeader[2] &&
                    $headerD === $expectedHeader[3]
                )) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Header file Excel tidak sesuai. Pastikan kolom A sampai D berturut-turut: ' .
                            implode(', ', $expectedHeader) . '.' . "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }

                $insert = [];
                // Looping data mulai dari baris kedua (header berada di baris pertama)
                foreach ($data as $rowIndex => $rowValue) {
                    if ($rowIndex == 1) {
                        continue; // Lewati header
                    }

                    $supplierId = trim($rowValue['A'] ?? '');
                    $userId     = trim($rowValue['B'] ?? '');
                    $barangId   = trim($rowValue['C'] ?? '');
                    $stokJumlah = trim($rowValue['D'] ?? '');

                    // Validasi: Semua kolom wajib terisi
                    if ($supplierId === '' || $userId === '' || $barangId === '' || $stokJumlah === '') {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex} tidak lengkap. Semua kolom wajib diisi." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    // Validasi: stok_jumlah harus numeric
                    if (!is_numeric($stokJumlah)) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: Nilai stok_jumlah harus berupa angka." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    // Validasi foreign key: supplier_id, user_id, dan barang_id harus ada
                    if (!SupplierModel::where('supplier_id', $supplierId)->exists()) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: Supplier dengan ID '{$supplierId}' tidak ditemukan." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }
                    if (!UserModel::where('user_id', $userId)->exists()) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: User dengan ID '{$userId}' tidak ditemukan." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }
                    if (!BarangModel::where('barang_id', $barangId)->exists()) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: Barang dengan ID '{$barangId}' tidak ditemukan." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    $insert[] = [
                        'supplier_id'  => $supplierId,
                        'user_id'      => $userId,
                        'barang_id'    => $barangId,
                        'stok_jumlah'  => (int)$stokJumlah,
                        'stok_tanggal' => now()->toDateString(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }

                if (count($insert) > 0) {
                    // Insert data ke database
                    StokModel::insert($insert);
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

            return redirect('/');
        }
    }

    public function export_excel()
    {
        // Ambil data stok beserta relasi (pastikan relasi sudah didefinisikan di model StokModel)
        $stok = StokModel::with(['supplier', 'user', 'barang'])
            ->orderBy('stok_tanggal', 'desc')
            ->get();

        // Buat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Supplier');
        $sheet->setCellValue('C1', 'User');
        $sheet->setCellValue('D1', 'Barang');
        $sheet->setCellValue('E1', 'Stok Jumlah');
        $sheet->setCellValue('F1', 'Stok Tanggal');

        // Buat header bold
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Isi data stok
        $no = 1;
        $row = 2;
        foreach ($stok as $s) {
            $sheet->setCellValue('A' . $row, $no);
            // Pastikan untuk menyesuaikan field nama supplier sesuai dengan model SupplierModel Anda (misalnya: nama_supplier)
            $sheet->setCellValue('B' . $row, $s->supplier ? $s->supplier->nama_supplier : '');
            // Untuk user, gunakan field nama, sesuai dengan model UserModel
            $sheet->setCellValue('C' . $row, $s->user ? $s->user->nama : '');
            // Untuk barang, gunakan field barang_nama, sesuai dengan model BarangModel
            $sheet->setCellValue('D' . $row, $s->barang ? $s->barang->barang_nama : '');
            $sheet->setCellValue('E' . $row, $s->stok_jumlah);
            $sheet->setCellValue('F' . $row, $s->stok_tanggal);
            $no++;
            $row++;
        }

        // Set auto-size untuk kolom A sampai F
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set judul sheet
        $sheet->setTitle('Data Stok');

        // Buat writer untuk file Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Stok ' . date('Y-m-d H:i:s') . '.xlsx';

        // Atur header HTTP untuk file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        // Tampilkan file Excel untuk diunduh
        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        // Ambil data stok beserta relasi supplier, user, dan barang
        $stok = StokModel::with(['supplier', 'user', 'barang'])
            ->orderBy('stok_tanggal', 'desc')
            ->get();

        // Muat view export PDF (pastikan view "stok.export_pdf" sudah dibuat)
        $pdf = Pdf::loadView('stok.export_pdf', ['stok' => $stok]);
        $pdf->setPaper('a4', 'portrait');       // Set ukuran kertas A4 dan orientasi portrait
        $pdf->setOption("isRemoteEnabled", true); // Aktifkan opsi remote jika ada gambar dari URL

        return $pdf->stream('Data Stok ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
