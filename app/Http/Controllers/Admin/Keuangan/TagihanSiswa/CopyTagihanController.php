<?php

namespace App\Http\Controllers\Admin\Keuangan\TagihanSiswa;

use App\Http\Controllers\Controller;

class CopyTagihanController extends Controller
{
    public string $title = "Keuangan";
    public string $mainTitle = "Tagihan Siswa";
    public string $dataTitle = "Copy Tagihan";

    public function index()
    {
        $data["title"] = $this->title;
        $data["mainTitle"] = $this->mainTitle;
        $data["dataTitle"] = $this->dataTitle;

        return view("admin.keuangan.tagihan_siswa.copy_tagihan.index", $data);
    }
}
