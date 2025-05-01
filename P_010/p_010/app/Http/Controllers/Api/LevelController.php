<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\LevelModel;

class LevelController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Mengambil semua data level.');
        return LevelModel::all();
    }

    public function store(Request $request)
    {
        $level = LevelModel::create($request->all());
        Log::info('Level baru dibuat.', ['data' => $level]);
        return response()->json($level, 201);
    }

    public function show(LevelModel $level)
    {
        Log::info('Menampilkan data level.', ['id' => $level->id]);
        return response()->json($level);
    }

    public function update(Request $request, LevelModel $level)
    {
        $oldData = $level->toArray();
        $level->update($request->all());
        Log::info('Data level diperbarui.', [
            'id' => $level->id,
            'sebelum' => $oldData,
            'sesudah' => $level
        ]);
        return response()->json($level);
    }

    public function destroy(LevelModel $level)
    {
        $id = $level->id;
        $level->delete();
        Log::warning("Level dengan ID {$id} dihapus.");
        return response()->json([
            'success' => true,
            'message' => 'Level deleted successfully',
        ]);
    }
}
