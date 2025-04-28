<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LevelModel;
use App\Modules\DataTables\LevelDataTable;
use App\Modules\ViewData\LevelIndexViewData;
use App\Services\Interfaces\LevelServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LevelController extends Controller
{
    protected $levelService;

    public function __construct(LevelServiceInterface $levelService)
    {
        $this->levelService = $levelService;
    }

    public function index()
    {
        $viewData = LevelIndexViewData::get();
        $viewData['levels'] = $this->levelService->getAllLevels();
        return view('level.index', $viewData);
    }

    public function list(Request $request, LevelDataTable $dataTable)
    {
        $filter = [
            'level_id' => $request->level_id,
        ];

        return $dataTable->render($filter)->make(true);
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

            $result = $this->levelService->storeLevel($request->only(['level_kode', 'level_nama']));

            return response()->json($result);
        }

        return redirect('/');
    }


    public function edit_ajax(string $id)
    {
        $level = $this->levelService->getLevelById($id);

        if (!$level) {
            return response()->json([
                'status' => false,
                'message' => 'Data level tidak ditemukan',
            ]);
        }

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

            $level = $this->levelService->getLevelById($id);

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
        $level = $this->levelService->getLevelById($id);

        if (!$level) {
            return response()->json([
                'status' => false,
                'message' => 'Data level tidak ditemukan',
            ]);
        }

        return view('level.confirm_ajax', compact('level'));
    }

    public function delete_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $level = $this->levelService->getLevelById($id);
            if ($level) {
                try {
                    $level->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Data level berhasil dihapus',
                    ]);
                } catch (QueryException $e) {
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

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_level' => ['required', 'mimes:xlsx', 'max:2048'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.' . "\n" . 'Mohon ikuti instruksi di template.',
                    'msgField' => $validator->errors(),
                ]);
            }

            try {
                $result = $this->levelService->importFromExcel($request->file('file_level'));
                return response()->json($result);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                ]);
            }
        }

        return redirect('/');
    }


    public function export_excel()
    {
        return $this->levelService->exportToExcel();
    }

    public function export_pdf()
    {
        return $this->levelService->exportToPDF();
    }
}
