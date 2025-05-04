<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:m_user,username',
            'nama' => 'required',
            'password' => 'required|min:6|confirmed',
            'level_id' => 'required',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $photo = $request->file('photo');
        $filename = uniqid() . '_' . time() . '.' . $photo->getClientOriginalExtension();
        $photo->storeAs('images/profiles', $filename, 'public');

        // Simpan user
        $user = UserModel::create([
            'username' => $request->username,
            'nama' => $request->nama,
            'password' => bcrypt($request->password),
            'level_id' => $request->level_id,
            'photo' => $filename,
        ]);

        if ($user) {
            return response()->json([
                'status' => true,
                'user' => $user,
            ], 201);
        }

        return response()->json([
            'success' => false,
        ], 409);
    }
}
