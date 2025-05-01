<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BarangModel;
use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object) [
            'title' => 'Daftar Barang',
            'list' => ['Home', 'Barang'],
        ];

        $page = (object) [
            'title' => 'Daftar barang dalam sistem',
        ];

        $activeMenu = 'barang';

        $kategori = KategoriModel::all();

        return view('barang.index', compact('breadcrumbs', 'page', 'activeMenu', 'kategori'));
    }

    public function list(Request $request)
    {
        $barangs = BarangModel::with('kategori')->select('m_barang.*');

        if ($request->filled('kategori_id')) {
            $barangs->where('kategori_id', $request->kategori_id);
        }

        return DataTables::of($barangs)
            ->addIndexColumn()
            ->addColumn('kategori', function ($barang) {
                return $barang->kategori ? $barang->kategori->kategori_nama : '-';
            })
            ->addColumn('harga_beli', function ($barang) {
                return format_rupiah($barang->harga_beli);
            })
            ->addColumn('harga_jual', function ($barang) {
                return format_rupiah($barang->harga_jual);
            })
            ->addColumn('aksi', function ($barang) {

                $btn = '<button onclick="modalAction(\'' . url('/barang/' . $barang->barang_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/barang/' . $barang->barang_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/barang/' . $barang->barang_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $kategori = KategoriModel::all();

        return view('barang.create_ajax', compact('kategori'));
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_id' => 'required|integer',
                'barang_kode' => 'required|string|max:50|unique:m_barang,barang_kode',
                'barang_nama' => 'required|string|max:100',
                'harga_beli' => 'required|numeric',
                'harga_jual' => 'required|numeric',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(),
                ]);
            }

            BarangModel::create([
                'kategori_id' => $request->kategori_id,
                'barang_kode' => $request->barang_kode,
                'barang_nama' => $request->barang_nama,
                'harga_beli' => $request->harga_beli,
                'harga_jual' => $request->harga_jual,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data barang berhasil disimpan.',
            ]);
        }

        return redirect('/');
    }

    public function show_ajax($id)
    {
        $barang = BarangModel::with('kategori')->find($id);

        return view('barang.show_ajax', compact('barang'));
    }

    public function edit_ajax($id)
    {
        $barang = BarangModel::find($id);
        $kategori = KategoriModel::all();

        return view('barang.edit_ajax', compact('barang', 'kategori'));
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_id' => 'required|integer',
                'barang_kode' => 'required|string|max:50|unique:m_barang,barang_kode,' . $id . ',barang_id',
                'barang_nama' => 'required|string|max:100',
                'harga_beli' => 'required|numeric',
                'harga_jual' => 'required|numeric',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(),
                ]);
            }

            $barang = BarangModel::find($id);
            if ($barang) {
                $barang->update([
                    'kategori_id' => $request->kategori_id,
                    'barang_kode' => $request->barang_kode,
                    'barang_nama' => $request->barang_nama,
                    'harga_beli' => $request->harga_beli,
                    'harga_jual' => $request->harga_jual,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Data barang berhasil diperbarui.',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data barang tidak ditemukan.',
                ]);
            }
        }

        return redirect('/');
    }

    public function confirm_ajax($id)
    {
        $barang = BarangModel::find($id);

        return view('barang.confirm_ajax', compact('barang'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $barang = BarangModel::find($id);
            if ($barang) {
                try {
                    $barang->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data barang berhasil dihapus.',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data barang gagal dihapus karena masih terkait dengan data lain.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data barang tidak ditemukan.',
                ]);
            }
        }

        return redirect('/');
    }

    public function import()
    {
        return view('barang.import');
    }

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            // Validasi file: harus .xlsx dengan ukuran maksimal 2MB
            $rules = [
                'file_barang' => ['required', 'mimes:xlsx', 'max:2048'],
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
                $file = $request->file('file_barang');
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
                $headerE = strtolower(str_replace(' ', '_', trim($data[1]['E'] ?? '')));
                $expectedHeader = ['kategori_id', 'barang_kode', 'barang_nama', 'harga_beli', 'harga_jual'];
                if (!(
                    $headerA === $expectedHeader[0] &&
                    $headerB === $expectedHeader[1] &&
                    $headerC === $expectedHeader[2] &&
                    $headerD === $expectedHeader[3] &&
                    $headerE === $expectedHeader[4]
                )) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Header file Excel tidak sesuai. Pastikan kolom A sampai E berturut-turut: ' .
                            implode(', ', $expectedHeader) . '.' . "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }

                $insert = [];
                foreach ($data as $rowIndex => $rowValue) {
                    if ($rowIndex == 1) {
                        continue; // Lewati header
                    }

                    if (
                        empty(trim($rowValue['A'] ?? '')) &&
                        empty(trim($rowValue['B'] ?? '')) &&
                        empty(trim($rowValue['C'] ?? '')) &&
                        empty(trim($rowValue['D'] ?? '')) &&
                        empty(trim($rowValue['E'] ?? ''))
                    ) {
                        continue;
                    }

                    $kategoriId = trim($rowValue['A'] ?? '');
                    $barangKode = trim($rowValue['B'] ?? '');
                    $barangNama = trim($rowValue['C'] ?? '');
                    $hargaBeli  = trim($rowValue['D'] ?? '');
                    $hargaJual  = trim($rowValue['E'] ?? '');

                    // Validasi: Semua kolom wajib terisi
                    if ($kategoriId === '' || $barangKode === '' || $barangNama === '' || $hargaBeli === '' || $hargaJual === '') {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex} tidak lengkap. Semua kolom wajib diisi." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    // Validasi: harga_beli dan harga_jual harus numeric
                    if (!is_numeric($hargaBeli) || !is_numeric($hargaJual)) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: Nilai harga harus berupa angka." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    // Validasi foreign key: kategori_id harus ada
                    if (!KategoriModel::where('kategori_id', $kategoriId)->exists()) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: Kategori dengan ID '{$kategoriId}' tidak ditemukan." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    // Validasi duplikasi: Cek apakah barang dengan kode yang sama sudah ada
                    $existing = BarangModel::where('barang_kode', $barangKode)->first();
                    if ($existing) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: Barang dengan kode '{$barangKode}' sudah ada." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    $insert[] = [
                        'kategori_id' => $kategoriId,
                        'barang_kode' => $barangKode,
                        'barang_nama' => $barangNama,
                        'harga_beli'  => (float)$hargaBeli,
                        'harga_jual'  => (float)$hargaJual,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }

                if (count($insert) > 0) {
                    // Insert data ke tabel
                    BarangModel::insert($insert);

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
        // Ambil data barang yang akan diexport
        $barang = BarangModel::select('kategori_id', 'barang_kode', 'barang_nama', 'harga_beli', 'harga_jual')
            ->orderBy('kategori_id')
            ->with('kategori')
            ->get();

        // Buat objek Spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Barang');
        $sheet->setCellValue('C1', 'Nama Barang');
        $sheet->setCellValue('D1', 'Harga Beli');
        $sheet->setCellValue('E1', 'Harga Jual');
        $sheet->setCellValue('F1', 'Kategori');

        // Buat header bold
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Isi data
        $no = 1;
        $baris = 2;
        foreach ($barang as $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->barang_kode);
            $sheet->setCellValue('C' . $baris, $value->barang_nama);
            $sheet->setCellValue('D' . $baris, $value->harga_beli);
            $sheet->setCellValue('E' . $baris, $value->harga_jual);
            $sheet->setCellValue('F' . $baris, $value->kategori->kategori_nama);
            $baris++;
            $no++;
        }

        // Set auto size untuk kolom
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set title sheet
        $sheet->setTitle('Data Barang');

        // Buat writer untuk file Excel
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Barang ' . date('Y-m-d H:i:s') . '.xlsx';

        // Set header HTTP untuk file download
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
        $barang = BarangModel::select('kategori_id', 'barang_kode', 'barang_nama', 'harga_beli', 'harga_jual')
            ->orderBy('kategori_id')
            ->orderBy('barang_kode')
            ->with('kategori')
            ->get();


        $pdf = Pdf::loadView('barang.export_pdf', ['barang' => $barang]);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption("isRemoteEnabled", true);

        return $pdf->stream('Data Barang ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
