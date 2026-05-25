<?php

namespace App\Http\Controllers\Admin\Keuangan\Saldo;

use App\Http\Controllers\Controller;

class SaldoVirtualSakuController extends Controller
{
    public string $title = "Keuangan";
    public string $mainTitle = "Saldo";
    public string $dataTitle = "Saldo Virtual SAKU";

    public function index()
    {
        $data["title"] = $this->title;
        $data["mainTitle"] = $this->mainTitle;
        $data["dataTitle"] = $this->dataTitle;

        return view("admin.keuangan.saldo.saldo_virtual_saku.index", $data);
    }
}
