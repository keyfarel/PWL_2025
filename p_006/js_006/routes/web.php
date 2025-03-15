<?php

use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PenjualanDetailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\BarangController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index']);

Route::group(['prefix' => 'user'], function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/list', [UserController::class, 'list']);
    Route::get('/create_ajax', [UserController::class, 'create_ajax']);
    Route::post('/ajax', [UserController::class, 'store_ajax']);
    Route::get('/{id}/show_ajax', [UserController::class, 'show_ajax'])->name('user.show_ajax');
    Route::get('/{id}/edit_ajax', [UserController::class, 'edit_ajax']);
    Route::put('/{id}/update_ajax', [UserController::class, 'update_ajax']);
    Route::get('/{id}/delete_ajax', [UserController::class, 'confirm_ajax']);
    Route::delete('/{id}/delete_ajax', [UserController::class, 'delete_ajax']);
});

Route::prefix('level')->group(function () {
    Route::get('/', [LevelController::class, 'index']);
    Route::post('/list', [LevelController::class, 'list'])->name('level.list');
    Route::get('/create_ajax', [LevelController::class, 'create_ajax']);
    Route::post('/ajax', [LevelController::class, 'store_ajax']);
    Route::get('/{id}/show_ajax', [LevelController::class, 'show_ajax']);
    Route::get('/{id}/edit_ajax', [LevelController::class, 'edit_ajax']);
    Route::put('/{id}/update_ajax', [LevelController::class, 'update_ajax']);
    Route::get('/{id}/delete_ajax', [LevelController::class, 'confirm_ajax']);
    Route::delete('/{id}/delete_ajax', [LevelController::class, 'delete_ajax']);
});

Route::prefix('kategori')->group(function () {
    Route::get('/', [KategoriController::class, 'index']);
    Route::post('/list', [KategoriController::class, 'list'])->name('kategori.list');
    Route::get('/create', [KategoriController::class, 'create']);
    Route::post('/', [KategoriController::class, 'store']);
    Route::get('/{id}/edit', [KategoriController::class, 'edit']);
    Route::put('/{id}', [KategoriController::class, 'update']);
    Route::delete('/{id}', [KategoriController::class, 'destroy']);
});

Route::prefix('barang')->group(function () {
    Route::get('/', [BarangController::class, 'index']);
    Route::post('/list', [BarangController::class, 'list'])->name('barang.list');
    Route::get('/create', [BarangController::class, 'create']);
    Route::post('/', [BarangController::class, 'store']);
    Route::get('/{id}', [BarangController::class, 'show']);
    Route::get('/{id}/edit', [BarangController::class, 'edit']);
    Route::put('/{id}', [BarangController::class, 'update']);
    Route::delete('/{id}', [BarangController::class, 'destroy']);
});

Route::prefix('stok')->group(function () {
    Route::get('/', [StokController::class, 'index']);
    Route::post('/list', [StokController::class, 'list'])->name('stok.list');
    Route::get('/create', [StokController::class, 'create']);
    Route::post('/', [StokController::class, 'store']);
    Route::get('/{id}', [StokController::class, 'show']);
    Route::get('/{id}/edit', [StokController::class, 'edit']);
    Route::put('/{id}', [StokController::class, 'update']);
    Route::delete('/{id}', [StokController::class, 'destroy']);
});


Route::prefix('penjualan')->group(function () {
    Route::get('/', [PenjualanController::class, 'index']);
    Route::post('/list', [PenjualanController::class, 'list'])->name('penjualan.list');
    Route::get('/create', [PenjualanController::class, 'create']);
    Route::post('/', [PenjualanController::class, 'store']);
    Route::get('/{id}', [PenjualanController::class, 'show']);  // <-- Detail penjualan (header + detail)
    Route::get('/{id}/edit', [PenjualanController::class, 'edit']);
    Route::put('/{id}', [PenjualanController::class, 'update']);
    Route::delete('/{id}', [PenjualanController::class, 'destroy']);
});
