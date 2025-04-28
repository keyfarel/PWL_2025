<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupplierModel;
use App\Modules\ViewData\SupplierIndexViewData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SupplierController extends Controller
{
    public function index()
    {
        $viewData = SupplierIndexViewData::get();
        $viewData['supplier'] = SupplierModel::all();
        return view('supplier.index', $viewData);
    }

    public function list(Request $request)
    {
        $supplier = SupplierModel::select('supplier_id', 'supplier_kode', 'supplier_nama', 'supplier_alamat');

        if ($request->has('supplier_id') && $request->supplier_id != '') {
            $supplier->where('supplier_id', $request->supplier_id);
        }

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

    public function import()
    {
        return view('supplier.import');
    }


    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            // Validasi file: harus .xlsx dengan ukuran maksimal 2MB
            $rules = [
                'file_supplier' => ['required', 'mimes:xlsx', 'max:2048'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status'   => false,
                    'message'  => 'Validasi Gagal. Mohon ikuti instruksi di template.',
                    'msgField' => $validator->errors(),
                ]);
            }

            try {
                // Ambil file Excel yang di-upload
                $file = $request->file('file_supplier');
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray(null, true, true, true);

                if (count($data) <= 1) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Tidak ada data yang diimport.',
                    ]);
                }

                // Validasi header
                $headerA = strtolower(str_replace(' ', '_', trim($data[1]['A'] ?? '')));
                $headerB = strtolower(str_replace(' ', '_', trim($data[1]['B'] ?? '')));
                $headerC = strtolower(str_replace(' ', '_', trim($data[1]['C'] ?? '')));
                $expectedHeader = ['supplier_kode', 'supplier_nama', 'supplier_alamat'];

                if (!(
                    $headerA === $expectedHeader[0] &&
                    $headerB === $expectedHeader[1] &&
                    $headerC === $expectedHeader[2]
                )) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Header file Excel tidak sesuai. Pastikan kolom A sampai C berturut-turut: ' .
                            implode(', ', $expectedHeader) . '.',
                    ]);
                }

                $insert = [];
                foreach ($data as $rowIndex => $rowValue) {
                    if ($rowIndex == 1) continue;

                    $kode   = trim($rowValue['A'] ?? '');
                    $nama   = trim($rowValue['B'] ?? '');
                    $alamat = trim($rowValue['C'] ?? '');

                    if ($kode === '' || $nama === '' || $alamat === '') {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex} tidak lengkap. Semua kolom wajib diisi.",
                        ]);
                    }

                    // Cek duplikat berdasarkan supplier_kode
                    if (SupplierModel::where('supplier_kode', $kode)->exists()) {
                        continue; // Lewati jika sudah ada
                    }

                    $insert[] = [
                        'supplier_kode'  => $kode,
                        'supplier_nama'  => $nama,
                        'supplier_alamat' => $alamat,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }

                if (count($insert) > 0) {
                    SupplierModel::insert($insert);
                    return response()->json([
                        'status'  => true,
                        'message' => 'Data supplier berhasil diimport.',
                    ]);
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Semua data sudah tersedia. Tidak ada data baru yang diimport.',
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                ]);
            }
        }
    }

    public function export_excel()
    {
        // Ambil data supplier
        $suppliers = SupplierModel::orderBy('created_at', 'asc')->get();

        // Buat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Supplier');
        $sheet->setCellValue('C1', 'Nama Supplier');
        $sheet->setCellValue('D1', 'Alamat Supplier');

        // Buat header bold
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        // Isi data supplier
        $no = 1;
        $row = 2;
        foreach ($suppliers as $supplier) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $supplier->supplier_kode);
            $sheet->setCellValue('C' . $row, $supplier->supplier_nama);
            $sheet->setCellValue('D' . $row, $supplier->supplier_alamat);
            $no++;
            $row++;
        }

        // Set auto-size untuk kolom A sampai D
        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set judul sheet
        $sheet->setTitle('Data Supplier');

        // Buat writer untuk file Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Supplier ' . date('Y-m-d H-i-s') . '.xlsx';

        // Atur header HTTP untuk file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
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
        // Ambil semua supplier, urutkan berdasarkan kode
        $suppliers = SupplierModel::select('supplier_kode', 'supplier_nama', 'supplier_alamat')
            ->orderBy('created_at', 'asc')
            ->get();

        // Muat view export_pdf.blade.php
        $pdf = Pdf::loadView('supplier.export_pdf', ['suppliers' => $suppliers]);

        // Opsi kertas dan orientation
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        // Stream langsung ke browser
        $filename = 'Data_Supplier_' . date('Y-m-d_His') . '.pdf';
        return $pdf->stream($filename);
    }
}
