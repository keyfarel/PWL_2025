<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BarangModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function index()
    {
        Log::info('Mengambil semua data barang.');
        return BarangModel::with(['kategori'])->get();
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'kategori_id'  => 'required|exists:m_kategori,kategori_id',
                'barang_kode'  => 'required|string|max:20|unique:m_barang,barang_kode',
                'barang_nama'  => 'required|string|max:100',
                'harga_beli'   => 'required|numeric|min:0',
                'harga_jual'   => 'required|numeric|min:0',
                'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle file upload jika ada
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $filename = $image->hashName();

                $folder = 'images/barang/' . $validated['barang_kode'];
                Storage::disk('public')->putFileAs($folder, $image, $filename);

                $validated['image'] = $filename;
            }

            $barang = BarangModel::create($validated);

            Log::info('Barang baru berhasil dibuat.', ['data' => $barang]);

            return response()->json([
                'message' => 'Barang berhasil dibuat.',
                'data' => $barang
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Gagal membuat barang.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat membuat barang.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(BarangModel $barang)
    {
        Log::info('Menampilkan data barang.', ['id' => $barang->barang_id]);
        return response()->json($barang->load('kategori'));
    }

    public function update(Request $request, BarangModel $barang)
    {
        $oldData = $barang->toArray();
        $barang->update($request->all());
        Log::info('Barang diperbarui.', [
            'id' => $barang->barang_id,
            'sebelum' => $oldData,
            'sesudah' => $barang
        ]);
        return response()->json($barang);
    }

    public function destroy(BarangModel $barang): \Illuminate\Http\JsonResponse
    {
        try {
            $id = $barang->barang_id;

            $barang->delete();

            Log::warning("Barang dengan ID {$id} dihapus.");
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dihapus.'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error karena foreign key constraint
            if ($e->getCode() == 23000) {
                Log::error("Gagal menghapus barang dengan ID {$barang->barang_id} karena masih terhubung ke data lain.", [
                    'error' => $e->getMessage()
                ]);
                return response()->json([
                    'error' => 'Barang tidak dapat dihapus karena masih digunakan di transaksi lain.'
                ], 409);
            }

            // Tangani query error lainnya
            Log::error("Query exception saat menghapus barang dengan ID {$barang->barang_id}.", [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat menghapus barang.'
            ], 500);

        } catch (\Exception $e) {
            // Tangani general exception
            Log::error("Exception umum saat menghapus barang dengan ID {$barang->barang_id}.", [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan tak terduga.'
            ], 500);
        }
    }
}
