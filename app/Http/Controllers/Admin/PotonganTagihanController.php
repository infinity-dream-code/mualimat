<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\mst_kelas;
use App\Models\mst_tagihan;
use App\Models\mst_thn_aka;
use App\Models\scctbill;
use App\Models\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PotonganTagihanController extends Controller
{
    public string $title;
    public string $datasUrl;
    public string $detailDatasUrl;
    public string $columnsUrl;

    public function __construct()
    {
        $this->title = "Potongan Tagihan";
        $this->mainTitle = "Potongan Tagihan";
        $this->datasUrl = route("admin.data-tagihan.get-data");
        $this->columnsUrl = route("admin.data-tagihan.get-column");
    }

    public function index()
    {
        $data["title"] = $this->title;
        $data["mainTitle"] = $this->mainTitle;
        $data["columnsUrl"] = $this->columnsUrl;
        $data["datasUrl"] = $this->datasUrl;
        $data["post"] = mst_tagihan::select(["tagihan"])
            ->orderByRaw(
                "
                        CASE
                            WHEN kode BETWEEN '07' AND '12' THEN 0
                            WHEN kode BETWEEN '01' AND '06' THEN 1
                            ELSE 2
                        END,
                        kode ASC
                    ",
            )
            ->get();
        $data["thn_aka"] = mst_thn_aka::select(["thn_aka"])
            ->where("thn_aka", "!=", null)
            ->orderBy("thn_aka", "desc")
            ->get();
        $data["kelas"] = mst_kelas::get();

        return view("admin.potongan_tagihan.index", $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                "item_id" => ["required", "array"],
                "potongan" => ["required", "array"],
                "potongan.*.potongan" => ["required", 'regex:/^[0-9]+(\.[0-9]{3})*$/'],
                "potongan.deskripsi_potongan" => ["array"],
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

        $bills = scctbill::where('PAIDST', 0)
            ->whereIn("AA", $request->item_id)->get();
        $totalSelectedBill = count($request->item_id);
        if ($bills->count() != $totalSelectedBill) {
            return response()->json(["message" => "Tagihan yang dipilih tidak valid!"], 422);
        }

        try {
            DB::beginTransaction();
            foreach ($bills as $item => $bill ) {
                foreach ($request->potongan as $id => $value) {

                }
            }
            DB::commit();
            return response()->json(["message" => "Data potongan disimpan!"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Gagal menyimpan potognan tagihan", "error" => $e->getMessage()], 422);
        }
    }
}
