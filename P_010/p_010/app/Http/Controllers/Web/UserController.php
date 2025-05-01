<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LevelModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function index()
    {
        $breadcrumbs = (object) [
            'title' => 'Daftar user',
            'list' => ['Home', 'user'],
        ];

        $page = (object) [
            'title' => 'Daftar user yang terdaftar dalam sistem',
        ];

        $activeMenu = 'user';
        $level = LevelModel::all();

        return view('user.index', [
            'breadcrumbs' => $breadcrumbs,
            'page' => $page,
            'level' => $level,
            'activeMenu' => $activeMenu,
        ]);
    }

    public function edit_profile()
    {
        $user = Auth::user();
        $page = (object)[
            'title' => 'Edit Profil'
        ];
        $breadcrumbs = (object)[
            'title' => 'Edit Profil',
            'list'  => ['Home', 'Edit Profil']
        ];

        return view('user.edit_profile', compact('user', 'page', 'breadcrumbs'));
    }

    public function update_profile(Request $request, $id)
    {
        // Ambil data user
        $user = UserModel::findOrFail($id);

        // Validasi data
        $rules = [
            'username' => 'required|string|min:3|max:20|unique:m_user,username,' . $id . ',user_id',
            'nama'     => 'required|string|max:100',
            'photo'    => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'min:6|max:20';
        }
        $validated = $request->validate($rules);

        // Update username, nama, dan password (jika diisi)
        $user->username = $validated['username'];
        $user->nama     = $validated['nama'];
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Tangani file foto baru
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // Hapus foto lama jika ada, menggunakan disk 'public'
            if ($user->photo && Storage::disk('public')->exists('images/profiles/' . $user->photo)) {
                Storage::disk('public')->delete('images/profiles/' . $user->photo);
            }

            // Buat nama file baru yang unik
            $file     = $request->file('photo');
            $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Simpan foto baru ke folder 'images/profiles' pada disk public
            $file->storeAs('images/profiles', $filename, 'public');

            // Update kolom photo di database
            $user->photo = $filename;
        }

        // Simpan perubahan ke database
        $user->save();

        return response()->json(['success' => 'Data user berhasil diperbarui.']);
    }

    public function list(Request $request)
    {
        $users = UserModel::select('user_id', 'username', 'nama', 'level_id')
            ->with('level');

        if ($request->level_id) {
            $users->where('level_id', $request->level_id);
        }

        return DataTables::of($users)
            ->addIndexColumn() // menambahkan kolom index / no urut (default nama kolom: DT_RowIndex)
            ->addColumn('aksi', function ($user) { // menambahkan kolom aksi
                $btn = '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';

                $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';

                $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';

                return $btn;
            })
            ->rawColumns(['aksi']) // memberitahu bahwa kolom aksi adalah html
            ->make(true);
    }

    public function create_ajax()
    {
        $level = LevelModel::select('level_id', 'level_nama')->get();

        return view('user.create_ajax')
            ->with('level', $level);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_id' => 'required|integer',
                'username' => 'required|string|min:3|unique:m_user,username',
                'nama' => 'required|string|max:100',
                'password' => 'required|min:6',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            UserModel::create($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Data user berhasil disimpan',
            ]);
        }

        return redirect('/');
    }

    public function show_ajax(string $id)
    {
        $user = UserModel::with('level')->find($id);

        return view('user.show_ajax', compact('user'));
    }

    public function edit_ajax(string $id)
    {
        $user = UserModel::find($id);
        $level = LevelModel::select('level_id', 'level_nama')->get();

        return view('user.edit_ajax', [
            'user' => $user,
            'level' => $level,
        ]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'level_id' => 'required|integer',
                'username' => 'required|max:20|unique:m_user,username,' . $id . ',user_id',
                'nama' => 'required|max:100',
                'password' => 'nullable|min:6|max:20',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false, // respon json, true: berhasil, false: gagal
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(), // menunjukkan field mana yang error
                ]);
            }
            $check = UserModel::find($id);
            if ($check) {
                if (! $request->filled('password')) { // jika password tidak diisi, maka hapus dari request

                    $request->request->remove('password');
                }
                $check->update($request->all());

                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil diupdate',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan',
                ]);
            }
        }

        return redirect('/');
    }

    public function confirm_ajax(string $id)
    {
        $user = UserModel::find($id);

        return view('user.confirm_ajax', [
            'user' => $user,
        ]);
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $user = UserModel::find($id);
            if ($user) {
                try {
                    $user->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data user berhasil dihapus.',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Data user gagal dihapus karena masih terkait dengan data lain.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan',
                ]);
            }
        }

        return redirect('/');
    }

    public function import()
    {
        return view('user.import');
    }

    /**
     * Memproses file import user via AJAX.
     */
    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            // Validasi file: harus .xlsx dengan ukuran maksimal 2MB
            $rules = [
                'file_user' => ['required', 'mimes:xlsx', 'max:2048'],
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
                $file = $request->file('file_user');
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
                $expectedHeader = ['level_id', 'username', 'nama', 'password'];
                if (!($headerA === $expectedHeader[0] &&
                    $headerB === $expectedHeader[1] &&
                    $headerC === $expectedHeader[2] &&
                    $headerD === $expectedHeader[3])) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Header file Excel tidak sesuai. Pastikan kolom A sampai D berturut-turut: ' .
                            implode(', ', $expectedHeader) . '.' . "\n" . 'Mohon ikuti instruksi di template.',
                    ]);
                }

                $insert = [];
                foreach ($data as $rowIndex => $rowValue) {
                    if ($rowIndex == 1) {
                        continue; // Lewati header
                    }

                    $levelId  = trim($rowValue['A'] ?? '');
                    $username = trim($rowValue['B'] ?? '');
                    $nama     = trim($rowValue['C'] ?? '');
                    $password = trim($rowValue['D'] ?? '');

                    // Jika seluruh kolom kosong, lewati baris tersebut (misalnya, baris kosong di akhir file)
                    if ($levelId === '' && $username === '' && $nama === '' && $password === '') {
                        continue;
                    }

                    // Jika hanya sebagian kolom kosong, return error
                    if ($levelId === '' || $username === '' || $nama === '' || $password === '') {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex} tidak lengkap. Semua kolom wajib diisi." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    // Validasi: level_id harus ada di tabel level
                    if (!LevelModel::where('level_id', $levelId)->exists()) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: Level dengan ID '{$levelId}' tidak ditemukan." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    // Cek duplikat berdasarkan username
                    $existing = UserModel::where('username', $username)->first();
                    if ($existing) {
                        return response()->json([
                            'status'  => false,
                            'message' => "Data pada baris {$rowIndex}: User dengan username '{$username}' sudah ada." . "\n" . 'Mohon ikuti instruksi di template.',
                        ]);
                    }

                    $insert[] = [
                        'level_id'   => $levelId,
                        'username'   => $username,
                        'nama'       => $nama,
                        'password'   => Hash::make($password),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (count($insert) > 0) {
                    UserModel::insert($insert);
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
        // Ambil data user dengan relasi level
        $users = UserModel::with('level')->orderBy('username', 'asc')->get();

        // Buat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Level');
        $sheet->setCellValue('C1', 'Username');
        $sheet->setCellValue('D1', 'Nama');

        // Buat header bold
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        // Isi data user
        $no = 1;
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $no);
            // Jika relasi level tidak ada, tampilkan kosong
            $sheet->setCellValue('B' . $row, $user->level ? $user->level->level_nama : '');
            $sheet->setCellValue('C' . $row, $user->username);
            $sheet->setCellValue('D' . $row, $user->nama);
            $row++;
            $no++;
        }

        // Set auto-size untuk kolom A sampai D
        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set judul sheet
        $sheet->setTitle('Data User');

        // Buat writer untuk file Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data User ' . date('Y-m-d H:i:s') . '.xlsx';

        // Set header HTTP untuk file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        // Output file Excel ke browser
        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        // Ambil data user beserta relasi level
        $users = UserModel::with('level')->orderBy('username', 'asc')->get();

        // Muat view export PDF dengan data user
        $pdf = Pdf::loadView('user.export_pdf', ['users' => $users]);

        // Atur ukuran kertas dan orientasi
        $pdf->setPaper('a4', 'portrait');
        // Aktifkan opsi remote jika ada gambar dari URL
        $pdf->setOption("isRemoteEnabled", true);

        return $pdf->stream('Data User ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
