<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LevelController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object) [
            'title' => 'Daftar Level',
            'list' => ['Home', 'Level'],
        ];

        $page = (object) [
            'title' => 'Daftar level dalam sistem',
        ];

        $activeMenu = 'level';

        $levels = LevelModel::all();

        return view('level.index', compact('breadcrumbs', 'page', 'activeMenu', 'levels'));
    }

    public function list(Request $request)
    {
        $levels = LevelModel::select('level_id', 'level_kode', 'level_nama');

        if ($request->level_id) {
            $levels->where('level_id', $request->level_id);
        }

        return DataTables::of($levels)
            ->addIndexColumn()
            ->addColumn('aksi', function ($level) {
                $btn = '<button onclick="modalAction(\'' . url('/level/' . $level->level_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/level/' . $level->level_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        return view('level.create_ajax');
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_kode' => 'required|string|max:50|unique:m_level,level_kode',
                'level_nama' => 'required|string|max:100|unique:m_level,level_nama',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(),
                ]);
            }

            LevelModel::create([
                'level_kode' => $request->level_kode,
                'level_nama' => $request->level_nama,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data level berhasil disimpan.',
            ]);
        }

        return redirect('/');
    }

    public function edit_ajax(string $id)
    {
        $level = LevelModel::find($id);

        return view('level.edit_ajax', compact('level'));
    }

    public function update_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_kode' => 'required|string|max:50|unique:m_level,level_kode,' . $id . ',level_id',
                'level_nama' => 'required|string|max:100|unique:m_level,level_nama,' . $id . ',level_id',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(),
                ]);
            }

            $level = LevelModel::find($id);
            if ($level) {
                $level->update([
                    'level_kode' => $request->level_kode,
                    'level_nama' => $request->level_nama,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Data level berhasil diperbarui',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data level tidak ditemukan',
                ]);
            }
        }

        return redirect('/');
    }

    public function confirm_ajax(string $id)
    {
        $level = LevelModel::find($id);

        return view('level.confirm_ajax', compact('level'));
    }

    public function delete_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $level = LevelModel::find($id);
            if ($level) {
                try {
                    $level->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data level berhasil dihapus',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data level gagal dihapus karena masih terkait dengan data lain',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data level tidak ditemukan',
                ]);
            }
        }

        return redirect('/');
    }

    public function import()
    {
        return view('level.import');
    }

    /**
     * Proses import data level dari file Excel.
     */
    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            // Validasi file: harus .xlsx, ukuran maksimal 2MB
            $rules = [
                'file_level' => ['required', 'mimes:xlsx', 'max:2048'],
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
                $file = $request->file('file_level');
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray(null, true, true, true);

                // Pastikan ada minimal 2 baris (header + minimal 1 data)
                if (count($data) <= 1) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Tidak ada data yang diimport.' . "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }

                // Validasi header file
                $headerA = strtolower(str_replace(' ', '_', trim($data[1]['A'] ?? '')));
                $headerB = strtolower(str_replace(' ', '_', trim($data[1]['B'] ?? '')));
                $expectedHeader = ['level_kode', 'level_nama'];
                if (!($headerA === $expectedHeader[0] && $headerB === $expectedHeader[1])) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Header file Excel tidak sesuai. Pastikan kolom A dan B berturut-turut: ' .
                            implode(', ', $expectedHeader) . '.' . "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }

                $insert = [];
                // Looping data mulai dari baris kedua (baris pertama adalah header)
                foreach ($data as $rowIndex => $rowValue) {
                    if ($rowIndex == 1) {
                        continue; // skip header
                    }

                    $levelKode = trim($rowValue['A'] ?? '');
                    $levelNama = trim($rowValue['B'] ?? '');

                    // Pastikan kolom wajib terisi
                    if ($levelKode === '' || $levelNama === '') {
                        continue;
                    }

                    // Cek duplikat: periksa apakah level dengan kode atau nama tersebut sudah ada
                    $existing = LevelModel::where('level_kode', $levelKode)
                        ->orWhere('level_nama', $levelNama)
                        ->first();
                    if ($existing) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Level dengan kode '{$levelKode}' atau nama '{$levelNama}' sudah ada." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    $insert[] = [
                        'level_kode' => $levelKode,
                        'level_nama' => $levelNama,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (count($insert) > 0) {
                    // Masukkan data ke database
                    LevelModel::insert($insert);

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

    public function export_excel()
    {
        // Ambil data level yang akan diexport
        $level = LevelModel::select('level_kode', 'level_nama')
            ->orderBy('level_kode', 'ASC')
            ->get();

        // Buat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Level');
        $sheet->setCellValue('C1', 'Nama Level');

        // Buat header bold
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        // Isi data
        $no = 1;
        $row = 2;
        foreach ($level as $item) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $item->level_kode);
            $sheet->setCellValue('C' . $row, $item->level_nama);

            $no++;
            $row++;
        }

        // Set auto size untuk kolom A sampai C
        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set judul sheet
        $sheet->setTitle('Data Level');

        // Buat writer untuk file Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Level ' . date('Y-m-d H:i:s') . '.xlsx';

        // Set header untuk file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        // Output file ke browser
        $writer->save('php://output');
        exit;
    }
}
