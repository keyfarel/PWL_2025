<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Cache\RateLimiting\Limiter;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function postlogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:4|max:20',
            'password' => 'required|min:6|max:20',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            return redirect('login')->withErrors($validator)->withInput();
        }

        $credentials = $request->only('username', 'password');

        // Mengecek apakah IP sudah melebihi batas limit
        if (RateLimiter::tooManyAttempts('login-limit|' . $request->ip(), 5)) {
            // Dapatkan waktu reset limit
            $secondsLeft = RateLimiter::availableIn('login-limit|' . $request->ip());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Terlalu banyak percobaan login. Coba lagi setelah ' . $secondsLeft . ' detik.',
                    'seconds_left' => $secondsLeft,
                ]);
            }

            return redirect('login')->with('error', 'Terlalu banyak percobaan login. Coba lagi setelah ' . $secondsLeft . ' detik.');
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Login Berhasil',
                    'redirect' => url('/'),
                ]);
            }

            return redirect()->intended('/');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Login Gagal. Username atau password salah.',
            ]);
        }

        return redirect('login')->with('error', 'Username atau password salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('login');
    }
}
