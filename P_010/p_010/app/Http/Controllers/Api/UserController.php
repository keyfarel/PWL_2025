<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Exception;

class UserController extends Controller
{
    public function index()
    {
        Log::info('Mengambil semua data user.');
        return UserModel::with('level')->get();
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'level_id' => 'required|exists:m_level,level_id',
                'username' => 'required|string|max:50|unique:m_user,username',
                'nama' => 'required|string|max:100',
                'password' => 'required|string|min:6'
            ]);

            $user = UserModel::create($validated);

            Log::info('User baru dibuat.', ['data' => $user]);

            return response()->json([
                'message' => 'User berhasil dibuat.',
                'data' => $user
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal.',
                'details' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Gagal membuat user.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat membuat user.'
            ], 500);
        }
    }

    public function show(UserModel $user)
    {
        try {
            Log::info('Menampilkan data user.', ['id' => $user->user_id]);
            return response()->json($user->load('level'));
        } catch (Exception $e) {
            Log::error('Gagal menampilkan user.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil data user.'
            ], 500);
        }
    }

    public function update(Request $request, UserModel $user)
    {
        try {
            $validated = $request->validate([
                'level_id' => 'sometimes|exists:m_level,level_id',
                'username' => 'sometimes|string|max:50|unique:m_user,username,' . $user->user_id . ',user_id',
                'nama' => 'sometimes|string|max:100',
                'password' => 'sometimes|string|min:6'
            ]);

            $oldData = $user->toArray();
            $user->update($validated);

            Log::info('User diperbarui.', [
                'id' => $user->user_id,
                'sebelum' => $oldData,
                'sesudah' => $user
            ]);

            return response()->json([
                'message' => 'User berhasil diperbarui.',
                'data' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal.',
                'details' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Gagal memperbarui user.', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat memperbarui user.'
            ], 500);
        }
    }

    public function destroy(UserModel $user)
    {
        try {
            $id = $user->user_id;
            $user->delete();

            Log::warning("User dengan ID {$id} dihapus.");

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.'
            ]);

        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                Log::error("Gagal menghapus user dengan ID {$user->user_id} karena masih digunakan.", [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'error' => 'User tidak dapat dihapus karena masih digunakan dalam data lain.'
                ], 409);
            }

            Log::error("Query exception saat menghapus user.", ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat menghapus user.'
            ], 500);

        } catch (Exception $e) {
            Log::error("Exception umum saat menghapus user.", ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Terjadi kesalahan tak terduga.'
            ], 500);
        }
    }
}
