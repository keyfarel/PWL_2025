<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PenjualanModel;

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
        $penjualan = PenjualanModel::select('penjualan_id', 'penjualan_kode', 'total_harga', 'pembeli', 'penjualan_tanggal', 'user_id')->with('user');

        return DataTables::of($penjualan)
            ->addIndexColumn()
            ->addColumn('user_name', function ($p) {
                return $p->user ? $p->user->nama : '-';
            })
            ->addColumn('total_harga', function ($p) {
                return format_rupiah($p->total_harga);
            })
            ->addColumn('aksi', function ($p) {
                $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $p->penjualan_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';

                if (auth()->user()->level && auth()->user()->level->level_kode === 'ADM') {
                    $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $p->penjualan_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $p->penjualan_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                }

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        // Ambil data user dari session login (biasanya digunakan di hidden input)
        $user = auth()->user();

        // Kirim view dengan data user jika diperlukan
        return view('penjualan.create_ajax', compact('user'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'pembeli' => 'required|string',
            'penjualan_tanggal' => 'required|date',
            'barang_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $penjualan = PenjualanModel::create([
                'user_id' => auth()->user()->user_id,
                'pembeli' => $request->pembeli,
                'penjualan_kode' => 'PNJ' . now()->format('YmdHis'),
                'penjualan_tanggal' => $request->penjualan_tanggal,
                'total_harga' => 0, // akan dihitung ulang nanti
            ]);

            $totalHarga = 0;

            foreach ($request->barang_id as $barangId) {
                $jumlah = $request->jumlah[$barangId];
                $barang = BarangModel::with('stok')->find($barangId);

                // Hitung total stok tersedia
                $stokTersedia = $barang->stok->sum('jumlah');
                if ($jumlah > $stokTersedia) {
                    throw new \Exception("Stok barang '{$barang->barang_nama}' tidak mencukupi.");
                }

                // Hitung harga
                $harga = $barang->harga_jual;
                $subtotal = $harga * $jumlah;
                $totalHarga += $subtotal;

                // Simpan detail penjualan
                PenjualanDetailModel::create([
                    'penjualan_id' => $penjualan->penjualan_id,
                    'barang_id' => $barangId,
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                ]);

                // Kurangi stok (bisa dari model stok)
                // contoh simple: stok terbaru saja dikurangi
                $stok = $barang->stok()->latest()->first();
                $stok->update(['jumlah' => $stok->jumlah - $jumlah]);
            }

            $penjualan->update(['total_harga' => $totalHarga]);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Data berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }


    public function edit_ajax($id)
    {
        $penjualan = PenjualanModel::find($id);
        return view('penjualan.edit_ajax', compact('penjualan'));
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $penjualan = PenjualanModel::find($id);
            if ($penjualan) {
                try {
                    // Validasi bisa ditambahkan sesuai kebutuhan
                    $penjualan->tanggal = $request->tanggal;
                    $penjualan->total = $request->total;
                    // Tambahkan field lainnya
                    $penjualan->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data penjualan berhasil diperbarui.'
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Gagal memperbarui data penjualan.'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data penjualan tidak ditemukan.'
                ]);
            }
        }

        return redirect('/');
    }


    public function show_ajax($id)
    {
        $penjualan = PenjualanModel::with(['user', 'detail.barang'])->find($id);
        return view('penjualan.detail.index', compact('penjualan'));
    }

    public function confirm_ajax($id)
    {
        $penjualan = PenjualanModel::find($id);
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

    public function export_pdf()
    {
        $penjualan = PenjualanModel::with(['user', 'detail.barang'])->orderBy('penjualan_tanggal', 'desc')->get();

        $pdf = Pdf::loadView('penjualan.export_pdf', ['penjualan' => $penjualan]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Data_Penjualan_' . date('Ymd_His') . '.pdf');
    }

    public function export_excel()
    {
        // Ambil data penjualan beserta relasi (pastikan relasi sudah didefinisikan di model PenjualanModel)
        $penjualan = PenjualanModel::with(['user', 'detail.barang'])
            ->orderBy('penjualan_tanggal', 'desc')
            ->get();

        // Buat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Penjualan');
        $sheet->setCellValue('C1', 'Pembeli');
        $sheet->setCellValue('D1', 'Tanggal');
        $sheet->setCellValue('E1', 'User');
        $sheet->setCellValue('F1', 'Total Harga');

        // Buat header bold
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Isi data penjualan
        $no = 1;
        $row = 2;
        foreach ($penjualan as $p) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $p->penjualan_kode);
            $sheet->setCellValue('C' . $row, $p->pembeli);
            $sheet->setCellValue('D' . $row, \Carbon\Carbon::parse($p->penjualan_tanggal)->format('Y-m-d'));
            $sheet->setCellValue('E' . $row, $p->user->nama ?? '-');
            $sheet->setCellValue('F' . $row, $p->total_harga);
            $no++;
            $row++;
        }

        // Set auto-size untuk kolom A sampai F
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set judul sheet
        $sheet->setTitle('Data Penjualan');

        // Buat writer untuk file Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data_Penjualan_' . date('Y-m-d_H-i-s') . '.xlsx';

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
}
