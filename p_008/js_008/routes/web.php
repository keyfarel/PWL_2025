<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'login'], function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/', [AuthController::class, 'postlogin']);
});

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', [WelcomeController::class, 'index']);

    Route::middleware(['authorize:ADM'])->group(function () {
        Route::prefix('level')->group(function () {

            Route::get('/', [LevelController::class, 'index']);
            Route::post('/list', [LevelController::class, 'list'])->name('level.list');
            Route::get('/create_ajax', [LevelController::class, 'create_ajax']);
            Route::post('/ajax', [LevelController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [LevelController::class, 'show_ajax']);
            Route::get('/{id}/edit_ajax', [LevelController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [LevelController::class, 'update_ajax']);
            Route::get('/import', [LevelController::class, 'import']);
            Route::post('/import_ajax', [LevelController::class, 'import_ajax']);
            Route::get('/{id}/delete_ajax', [LevelController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [LevelController::class, 'delete_ajax']);
        });

        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/list', [UserController::class, 'list']);
            Route::get('/create_ajax', [UserController::class, 'create_ajax']);
            Route::post('/ajax', [UserController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [UserController::class, 'show_ajax'])->name('user.show_ajax');
            Route::get('/{id}/edit_ajax', [UserController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [UserController::class, 'update_ajax']);
            Route::get('/import', [UserController::class, 'import']);
            Route::post('/import_ajax', [UserController::class, 'import_ajax']);
            Route::get('/{id}/delete_ajax', [UserController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [UserController::class, 'delete_ajax']);
        });
    });

    Route::middleware(['authorize:ADM,MNG'])->group(function () {

        Route::prefix('kategori')->group(function () {
            Route::get('/', [KategoriController::class, 'index']);
            Route::post('/list', [KategoriController::class, 'list'])->name('kategori.list');
            Route::get('/create_ajax', [KategoriController::class, 'create_ajax']);
            Route::post('/ajax', [KategoriController::class, 'store_ajax']);
            Route::get('/{id}/edit_ajax', [KategoriController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [KategoriController::class, 'update_ajax']);
            Route::get('/import', [KategoriController::class, 'import']);
            Route::post('/import_ajax', [KategoriController::class, 'import_ajax']);
            Route::get('/{id}/delete_ajax', [KategoriController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [KategoriController::class, 'delete_ajax']);
        });

        Route::prefix('barang')->group(function () {
            Route::get('/', [BarangController::class, 'index']);
            Route::post('/list', [BarangController::class, 'list'])->name('barang.list');
            Route::get('/create_ajax', [BarangController::class, 'create_ajax']);
            Route::post('/ajax', [BarangController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [BarangController::class, 'show_ajax'])->name('barang.show_ajax');
            Route::get('/{id}/edit_ajax', [BarangController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [BarangController::class, 'update_ajax']);
            Route::get('/import', [BarangController::class, 'import']);
            Route::post('/import_ajax', [BarangController::class, 'import_ajax']);
            Route::get('/{id}/delete_ajax', [BarangController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [BarangController::class, 'delete_ajax']);
        });
    });

    Route::middleware(['authorize:ADM,STF'])->group(function () {

        Route::prefix('stok')->group(function () {
            Route::get('/', [StokController::class, 'index']);
            Route::post('/list', [StokController::class, 'list'])->name('stok.list');
            Route::get('/create_ajax', [StokController::class, 'create_ajax']);
            Route::post('/ajax', [StokController::class, 'store_ajax']);
            Route::get('/{id}/show_ajax', [StokController::class, 'show_ajax'])->name('stok.show_ajax');
            Route::get('/{id}/edit_ajax', [StokController::class, 'edit_ajax']);
            Route::put('/{id}/update_ajax', [StokController::class, 'update_ajax']);
            Route::get('/import', [StokController::class, 'import']);
            Route::post('/import_ajax', [StokController::class, 'import_ajax']);
            Route::get('/{id}/delete_ajax', [StokController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [StokController::class, 'delete_ajax']);
        });
    });

    Route::middleware(['authorize:ADM,KSR'])->group(function () {

        Route::prefix('penjualan')->group(function () {
            Route::get('/', [PenjualanController::class, 'index']);
            Route::post('/list', [PenjualanController::class, 'list'])->name('penjualan.list');
            Route::get('/{id}/show_ajax', [PenjualanController::class, 'show_ajax']);
            Route::get('/{id}/delete_ajax', [PenjualanController::class, 'confirm_ajax']);
            Route::delete('/{id}/delete_ajax', [PenjualanController::class, 'delete_ajax']);
        });
    });
});
