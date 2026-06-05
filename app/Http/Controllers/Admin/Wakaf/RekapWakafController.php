<?php

namespace App\Http\Controllers\Admin\Wakaf;

use App\Http\Controllers\Controller;

class RekapWakafController extends Controller
{
    public string $title = 'Wakaf';
    public string $mainTitle = 'Wakaf';
    public string $dataTitle = 'Rekap Wakaf';

    public function index()
    {
        return view('admin.wakaf.rekap_wakaf.index', [
            'title' => $this->title,
            'mainTitle' => $this->mainTitle,
            'dataTitle' => $this->dataTitle,
        ]);
    }
}
