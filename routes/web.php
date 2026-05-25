<?php

use App\Http\Controllers\Admin\Rekap\RekapPenerimaan\RekapPenerimaanPerAkunController;
use App\Http\Controllers\Admin\Rekap\RekapTaighan\RekapTagihanPerAkunController;
use App\Http\Controllers\Admin\RekapSaldoController;
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

        Route::prefix("master-data")->name("master-data.")->group(function () {
            Route::prefix("master-kelas")
                ->name("master-kelas.")
                ->controller(\App\Http\Controllers\Admin\MasterData\MasterKelasController::class)
                ->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::resource("", \App\Http\Controllers\Admin\MasterData\MasterKelasController::class)->parameters(["" => "id"]);
                });

            Route::prefix("tahun-pelajaran")
                ->name("tahun-pelajaran.")
                ->controller(\App\Http\Controllers\Admin\MasterData\TahunPelajaranController::class)
                ->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                });
            Route::resource("tahun-pelajaran", \App\Http\Controllers\Admin\MasterData\TahunPelajaranController::class)->names("tahun-pelajaran");

            Route::prefix("export-import-data")
                ->name("export-import-data.")
                ->controller(\App\Http\Controllers\Admin\MasterData\ExportImportDataController::class)
                ->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::post("validate-data", "validateData")->name("validate-data");
                    Route::get("clear-data", "clearData")->name("clear-data");
                    Route::resource("", \App\Http\Controllers\Admin\MasterData\ExportImportDataController::class)->parameters(["" => "id"]);
                });

            Route::prefix("data-siswa")
                ->name("data-siswa.")
                ->controller(\App\Http\Controllers\Admin\MasterData\DataSiswaController::class)
                ->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::get("get-siswa", "getSiswa")->name("get-siswa");
                    Route::get("get-siswa-select2", "getSiswaSelect2")->name("get-siswa-select2");
                    Route::post("reset-login-android/{id}", "ResetLoginAndroid")->name("reset-login-android");
                    Route::post("set-status-siswa/{id}", "setStatusSiswa")->name("set-status-siswa");
                });
            Route::resource("data-siswa", \App\Http\Controllers\Admin\MasterData\DataSiswaController::class)->names("data-siswa");

            Route::prefix("setting-data-wa")
                ->name("setting-data-wa.")
                ->controller(\App\Http\Controllers\Admin\MasterData\SettingDataWaController::class)
                ->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::post("validate-data", "validateData")->name("validate-data");
                    Route::get("clear-data", "clearData")->name("clear-data");
                    Route::resource("", \App\Http\Controllers\Admin\MasterData\SettingDataWaController::class)->parameters(["" => "id"]);
                });

            Route::prefix("master-post")
                ->name("master-post.")
                ->controller(\App\Http\Controllers\Admin\MasterData\MasterPostController::class)
                ->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::resource("", \App\Http\Controllers\Admin\MasterData\MasterPostController::class)->parameters(["" => "id"]);
                });

            Route::prefix("beban-post")
                ->name("beban-post.")
                ->controller(\App\Http\Controllers\Admin\MasterData\BebanPostController::class)
                ->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::resource("", \App\Http\Controllers\Admin\MasterData\BebanPostController::class)->parameters(["" => "id"]);
                });

            Route::prefix("pindah-kelas")
                ->name("pindah-kelas.")
                ->controller(\App\Http\Controllers\Admin\MasterData\PindahKelasController::class)
                ->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::resource("", \App\Http\Controllers\Admin\MasterData\PindahKelasController::class)->parameters(["" => "id"]);
                });
        });

        Route::prefix("data-tagihan")
            ->name("data-tagihan.")
            ->group(function () {
                Route::controller(
                    \App\Http\Controllers\Admin\DataTagihanController::class,
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
                    Route::get("get-data-rekap", "getDataRekap")->name(
                        "get-data-rekap",
                    );
                    Route::resource(
                        "",
                        \App\Http\Controllers\Admin\DataTagihanController::class,
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
                    \App\Http\Controllers\Admin\DataPenerimaanController::class,
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
                        "getDataRekap",
                    )->name("get-data-rekap");
                    Route::resource(
                        "",
                        \App\Http\Controllers\Admin\DataPenerimaanController::class,
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
                    \App\Http\Controllers\Admin\Rekap\RekapPenerimaanHarianController::class,
                )->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::get(
                        "cetak-rekap-harian",
                        "cetakRekapPenerimaanHarian",
                    )->name("cetak-rekap-harian");
                    Route::resource(
                        "",
                        \App\Http\Controllers\Admin\Rekap\RekapPenerimaanHarianController::class,
                    )->parameters(["" => "id"]);
                });
            });

        Route::prefix("cek-pelunasan")
            ->name("cek-pelunasan.")
            ->controller(\App\Http\Controllers\Admin\CekPelunasanController::class)
            ->group(function () {
                Route::get("get-data", "getData")->name("get-data");
                Route::get("get-column", "getColumn")->name("get-column");
                Route::get("cetak-kartu-siswa", "cetakKartuSiswa")->name(
                    "cetak-kartu-siswa",
                );
                Route::resource(
                    "",
                    \App\Http\Controllers\Admin\CekPelunasanController::class,
                )->parameters(["" => "id"]);
            });

        Route::prefix("rekap-cek-pelunasan")
            ->name("rekap-cek-pelunasan.")
            ->controller(\App\Http\Controllers\Admin\CekPelunasan\RekapCekPelunasanController::class)
            ->group(function () {
                Route::get("get-data", "getData")->name("get-data");
                Route::get("get-column", "getColumn")->name("get-column");
                Route::resource(
                    "",
                    \App\Http\Controllers\Admin\CekPelunasan\RekapCekPelunasanController::class,
                )->parameters(["" => "id"]);
            });

        Route::prefix("potongan-tagihan")
            ->name("potongan-tagihan.")
            ->controller(\App\Http\Controllers\Admin\PotonganTagihanController::class)
            ->group(function () {
                Route::get("get-data", "getData")->name("get-data");
                Route::get("get-column", "getColumn")->name("get-column");
                Route::get("cetak-kuitansi", "cetakKuitansi")->name("cetak-kuitansi");
                Route::resource(
                    "",
                    \App\Http\Controllers\Admin\PotonganTagihanController::class,
                )->parameters(["" => "id"]);
            });

        Route::prefix("rekap-saldo")
            ->name("rekap-saldo.")
            ->group(function () {
                Route::controller(
                    RekapSaldoController::class,
                )->group(function () {
                    Route::get("get-data", "getData")->name("get-data");
                    Route::get("get-column", "getColumn")->name("get-column");
                    Route::get(
                        "get-data-rekap",
                        "getDataRekap",
                    )->name("get-data-rekap");
                    Route::resource(
                        "",
                        RekapSaldoController::class,
                    )->parameters(["" => "id"]);
                });
            });
    });
