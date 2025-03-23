<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    // Memproses request login melalui AJAX atau biasa
    public function postlogin(Request $request)
    {
        // Validasi input login
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:4|max:20',
            'password' => 'required|min:6|max:20'
        ]);

        if ($validator->fails()) {
            // Jika request berupa ajax/kirim JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors()
                ]);
            }
            return redirect('login')
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('username', 'password');

        // Cek autentikasi menggunakan field 'username'
        if (Auth::attempt($credentials)) {
            // Jika login sukses, regenerasi session untuk keamanan
            $request->session()->regenerate();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Login Berhasil',
                    'redirect' => url('/')
                ]);
            }
            return redirect()->intended('/');
        }

        // Jika login gagal
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Login Gagal. Username atau password salah.'
            ]);
        }
        return redirect('login')->with('error', 'Username atau password salah.');
    }

    // Fungsi logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login');
    }
}
