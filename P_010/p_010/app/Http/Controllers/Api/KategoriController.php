<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriModel;
use Illuminate\Support\Facades\Log;

class KategoriController extends Controller
{
    public function index()
    {
        Log::info('Mengambil semua data kategori.');
        return KategoriModel::with('barang')->get();
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'kategori_kode' => 'required|string|max:10|unique:m_kategori,kategori_kode',
                'kategori_nama' => 'required|string|max:100',
            ]);

            $kategori = KategoriModel::create($validated);

            Log::info('Kategori baru berhasil dibuat.', ['data' => $kategori]);

            return response()->json([
                'message' => 'Kategori berhasil dibuat.',
                'data' => $kategori
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Gagal membuat kategori.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat membuat kategori.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(KategoriModel $kategori)
    {
        Log::info('Menampilkan data kategori.', ['id' => $kategori->kategori_id]);
        return response()->json($kategori->load('barang'));
    }

    public function update(Request $request, KategoriModel $kategori)
    {
        $oldData = $kategori->toArray();
        $kategori->update($request->all());
        Log::info('Kategori diperbarui.', [
            'id' => $kategori->kategori_id,
            'sebelum' => $oldData,
            'sesudah' => $kategori
        ]);
        return response()->json($kategori);
    }

    public function destroy(KategoriModel $kategori): \Illuminate\Http\JsonResponse
    {
        try {
            $id = $kategori->kategori_id;

            $kategori->delete();

            Log::warning("Kategori dengan ID {$id} dihapus.");

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus.'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Constraint violation (masih digunakan barang)
                Log::error("Gagal menghapus kategori dengan ID {$kategori->kategori_id} karena masih digunakan pada barang.", [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'error' => 'Kategori tidak dapat dihapus karena masih digunakan oleh barang.'
                ], 409);
            }

            // Query error lainnya
            Log::error("Query exception saat menghapus kategori dengan ID {$kategori->kategori_id}.", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat menghapus kategori.'
            ], 500);

        } catch (\Exception $e) {
            Log::error("Exception umum saat menghapus kategori dengan ID {$kategori->kategori_id}.", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan tak terduga.'
            ], 500);
        }
    }
}
