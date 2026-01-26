<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PotonganTagihanController extends Controller
{
    public string $title;
    public string $datasUrl;
    public string $detailDatasUrl;
    public string $columnsUrl;

    public function __construct()
    {
        $this->title = "Rekap SPP";
//        $this->datasUrl = route("admin.rekap-spp.get-data");
        $this->detailDatasUrl = "";
//        $this->columnsUrl = route("admin.rekap-spp.get-column");
    }

    public  function  store(Request $request)
    {

    }
}
