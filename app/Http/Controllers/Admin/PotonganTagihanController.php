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
        $validator = Validator::make(
            $request->all(),
            [
                "item_id" => ["required", "array"],
                "potongan" => ["required", "array"],
                "potongan.*" => ["required", "array", 'regex:/^[0-9]+(\.[0-9]{3})*$/'],
                "deskripsi_potongan" => ["array", "string"],
            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes(),
        );

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            if ($validator->errors()->count() > 1) {
                $message = "{$message} Dan beberapa masalah validasi lainnya, silahkan periksa form anda!";
            }
            return response()->json(
                [
                    "message" => $message,
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

    }
}
