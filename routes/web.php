<?php

use App\Http\Controllers\Admin\Rekap\RekapPenerimaan\RekapPenerimaanPerAkunController;
use App\Http\Controllers\Admin\Rekap\RekapTaighan\RekapTagihanPerAkunController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RekapPenerimaanController;
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

Auth::routes([
    "register" => false,
]);
Route::get("/", [AuthController::class, "index"])->name("index");

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get("/reload-captcha", [AuthController::class, "reloadCaptcha"])->name(
    "reload-captcha",
);

Route::prefix("admin")
    ->name("admin.")
    ->middleware(["auth","check.roles:admin"])
    ->group(function () {
        Route::get("/", [AdminController::class, "index"])->name("index");

        Route::prefix("data-tagihan")
            ->name("data-tagihan.")
            ->group(function () {
                Route::controller(
                    \App\Http\Controllers\DataTagihanController::class,
                )->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::get("cetak-rekap", "cetak")->name("cetak-rekap");
                    Route::get(
                        "cetak-rekap-tagihan",
                        "cetakRekapTagihan",
                    )->name("cetak-rekap-tagihan");
                    Route::get("cetak-kartu-siswa", "cetakKartuSiswa")->name(
                        "cetak-kartu-siswa",
                    );
                    Route::resource(
                        "",
                        \App\Http\Controllers\DataTagihanController::class,
                    )->parameters(["" => "id"]);
                });
            });

        Route::prefix("rekap-tagihan")
            ->name("rekap-tagihan.")
            ->group(function () {
                Route::controller(
                    RekapTagihanPerAkunController::class,
                )->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::get("cetak-rekap", "cetakRekapPenerimaan")->name(
                        "cetak-rekap",
                    );
                    Route::get("cetak-kartu-siswa", "cetakKartuSiswa")->name(
                        "cetak-kartu-siswa",
                    );
                    Route::get(
                        "get-data-rekap",
                        "getRekapDataTagihan",
                    )->name("get-data-rekap");
                    Route::resource(
                        "",
                        RekapTagihanPerAkunController::class,
                    )->parameters(["" => "id"]);
                });
            });

        Route::prefix("data-penerimaan")
            ->name("data-penerimaan.")
            ->group(function () {
                Route::controller(
                    \App\Http\Controllers\DataPenerimaanController::class,
                )->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::get("cetak-rekap", "cetakRekapPenerimaan")->name(
                        "cetak-rekap",
                    );
                    Route::get(
                        "cetak-tagihan-dibayar",
                        "cetakPembayaran",
                    )->name("cetak-tagihan-dibayar");
                    Route::get("cetak-kartu-siswa", "cetakKartuSiswa")->name(
                        "cetak-kartu-siswa",
                    );
                    Route::get(
                        "get-data-rekap",
                        "getRekapDataPenerimaan",
                    )->name("get-data-rekap");
                    Route::resource(
                        "",
                        \App\Http\Controllers\DataPenerimaanController::class,
                    )->parameters(["" => "id"]);
                });
            });

        Route::prefix("rekap-penerimaan")
            ->name("rekap-penerimaan.")
            ->group(function () {
                Route::controller(
                    RekapPenerimaanPerAkunController::class,
                )->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::get("cetak-rekap", "cetakRekapPenerimaan")->name(
                        "cetak-rekap",
                    );
                    Route::get("cetak-kartu-siswa", "cetakKartuSiswa")->name(
                        "cetak-kartu-siswa",
                    );
                    Route::get(
                        "get-data-rekap",
                        "getRekapDataPenerimaan",
                    )->name("get-data-rekap");
                    Route::resource(
                        "",
                        RekapPenerimaanPerAkunController::class,
                    )->parameters(["" => "id"]);
                });
            });

        Route::prefix("rekap-penerimaan-harian")
            ->name("rekap-penerimaan-harian.")
            ->group(function () {
                Route::controller(
                    \App\Http\Controllers\RekapPenerimaanController::class,
                )->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::get(
                        "cetak-rekap-harian",
                        "cetakRekapPenerimaanHarian",
                    )->name("cetak-rekap-harian");
                    Route::resource(
                        "",
                        \App\Http\Controllers\RekapPenerimaanController::class,
                    )->parameters(["" => "id"]);
                });
            });

        Route::prefix("cek-pelunasan")
            ->name("cek-pelunasan.")
            ->controller(\App\Http\Controllers\CekPelunasanController::class)
            ->group(function () {
                Route::get("get-data", "getData")->name("get-data");
                Route::get("get-column", "getColumn")->name("get-column");
                Route::get("cetak-kartu-siswa", "cetakKartuSiswa")->name(
                    "cetak-kartu-siswa",
                );
                Route::resource(
                    "",
                    \App\Http\Controllers\CekPelunasanController::class,
                )->parameters(["" => "id"]);
            });
    });
