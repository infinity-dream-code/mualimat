<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AuthController::class, 'index'] )->name('index');
Route::get('/login', [AuthController::class, 'index'] )->name('login');
Route::post('/login', [AuthController::class, 'login'] )->name('login');
Route::get('/logout', [AuthController::class, 'logout'] )->name('logout');
Route::get('/reload-captcha', [AuthController::class, 'reloadCaptcha'] )->name('reload-captcha');

Route::prefix('admin')->name('admin.')->middleware('check.session')->group(function () {
    Route::get('/', [AdminController::class,'index'])->name('index');

    Route::prefix('data-penerimaan')->name('data-penerimaan.')->group(function () {
        Route::controller(\App\Http\Controllers\DataPenerimaanController::class)->group(function () {
            Route::get('get-data', 'getData')->name('get-data');
            Route::get('get-column', 'getColumn')->name('get-column');
            Route::get('cetak-rekap', 'cetak')->name('cetak-rekap');
            Route::get('cetak-tagihan-dibayar', 'cetakPembayaran')->name('cetak-tagihan-dibayar');
            Route::resource('', \App\Http\Controllers\DataPenerimaanController::class)->parameters(['' => 'id']);
        });
    });

    Route::prefix('data-tagihan')->name('data-tagihan.')->group(function () {
        Route::controller(\App\Http\Controllers\DataTagihanController::class)->group(function () {
            Route::get('get-data', 'getData')->name('get-data');
            Route::get('get-column', 'getColumn')->name('get-column');
            Route::get('cetak-rekap', 'cetak')->name('cetak-rekap');
            Route::resource('', \App\Http\Controllers\DataTagihanController::class)->parameters(['' => 'id']);
        });
    });
});
