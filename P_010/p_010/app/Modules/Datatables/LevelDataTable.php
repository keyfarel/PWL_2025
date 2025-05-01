<?php

namespace App\Modules\Datatables;

use App\Services\Interfaces\LevelServiceInterface;
use Yajra\DataTables\DataTables;

class LevelDataTable
{
    protected $levelService;

    public function __construct(LevelServiceInterface $levelService)
    {
        $this->levelService = $levelService;
    }

    public function render(array $filter = [])
    {
        $query = $this->levelService->getListLevels($filter);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', function ($level) {
                $btn = '<button onclick="modalAction(\'' . url('/level/' . $level->level_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/level/' . $level->level_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button>';
                return $btn;
            })
            ->rawColumns(['aksi']);
    }
}
