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
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = UserModel::create([
                'username' => $request->username,
                'nama' => $request->nama,
                'password' => bcrypt($request->password),
                'level_id' => $request->level_id,
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
