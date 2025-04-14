<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ThrottleRequestsException) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Terlalu banyak percobaan login. Coba lagi dalam beberapa saat.',
                ], 429);
            }

            return redirect()->back()
                ->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi nanti.')
                ->withInput();
        }

        return parent::render($request, $exception);
    }
}
