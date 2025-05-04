<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenjualanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenjualanController extends Controller
{
    public function index()
    {
        Log::info('Mengambil semua data penjualan.');
        return PenjualanModel::with(['user', 'detail'])->get();
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:m_user,user_id',
                'pembeli'          => 'required|string|max:100',
                'penjualan_kode'   => 'required|string|max:20|unique:t_penjualan,penjualan_kode',
                'penjualan_tanggal'=> 'required|date',
                'total_harga'      => 'required|numeric|min:0',
                'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Upload image jika ada
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $filename = $image->hashName();

                $folder = 'images/penjualan/' . $validated['penjualan_kode'];
                Storage::disk('public')->putFileAs($folder, $image, $filename);

                $validated['image'] = $filename;
            }

            $penjualan = PenjualanModel::create($validated);

            Log::info('Penjualan baru berhasil dibuat.', ['data' => $penjualan]);

            return response()->json([
                'message' => 'Penjualan berhasil dibuat.',
                'data' => $penjualan
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Gagal membuat penjualan.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat membuat penjualan.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(PenjualanModel $penjualan)
    {
        Log::info('Menampilkan data penjualan.', ['id' => $penjualan->penjualan_id]);
        return response()->json($penjualan->load(['user', 'detail']));
    }

    public function update(Request $request, PenjualanModel $penjualan)
    {
        try {
            $validated = $request->validate([
                'user_id'          => 'sometimes|exists:users,user_id',
                'pembeli'          => 'sometimes|string|max:100',
                'penjualan_kode'   => 'sometimes|string|max:20|unique:t_penjualan,penjualan_kode,' . $penjualan->penjualan_id . ',penjualan_id',
                'penjualan_tanggal'=> 'sometimes|date',
                'total_harga'      => 'sometimes|numeric|min:0',
                'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $oldData = $penjualan->toArray();

            // Handle file upload jika ada
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $filename = $image->hashName();
                $folder = 'images/penjualan/' . ($validated['penjualan_kode'] ?? $penjualan->penjualan_kode);

                Storage::disk('public')->putFileAs($folder, $image, $filename);

                $validated['image'] = $filename;
            }

            $penjualan->update($validated);

            Log::info('Penjualan diperbarui.', [
                'id' => $penjualan->penjualan_id,
                'sebelum' => $oldData,
                'sesudah' => $penjualan
            ]);

            return response()->json([
                'message' => 'Penjualan berhasil diperbarui.',
                'data' => $penjualan
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Gagal memperbarui penjualan.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat memperbarui penjualan.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PenjualanModel $penjualan): \Illuminate\Http\JsonResponse
    {
        try {
            $id = $penjualan->penjualan_id;

            $penjualan->delete();

            Log::warning("Penjualan dengan ID {$id} dihapus.");
            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil dihapus.'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                Log::error("Gagal menghapus penjualan dengan ID {$penjualan->penjualan_id} karena masih terhubung ke data lain.", [
                    'error' => $e->getMessage()
                ]);
                return response()->json([
                    'error' => 'Penjualan tidak dapat dihapus karena masih digunakan di transaksi lain.'
                ], 409);
            }

            Log::error("Query exception saat menghapus penjualan.", ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat menghapus penjualan.'
            ], 500);

        } catch (\Exception $e) {
            Log::error("Exception umum saat menghapus penjualan.", ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan tak terduga.'
            ], 500);
        }
    }
}
