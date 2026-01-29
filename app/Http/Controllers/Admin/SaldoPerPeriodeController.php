<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\mst_kelas;
use App\Models\mst_tagihan;
use App\Models\mst_thn_aka;
use App\Models\scctbill;
use App\Models\scctcust;
use Carbon\Carbon;
use Illuminate\Http\Request;
use function App\Http\Controllers\Admin\Rekap\RekapPenerimaan\;

class SaldoPerPeriodeController extends Controller
{
    private string $title;
    private string $datasUrl;
    private string $columnsUrl;
    private string $mainTitle;

    public function __construct()
    {
        $this->title = "Saldo Per Periode";
        $this->mainTitle = "Saldo Per Periode";
        $this->datasUrl = route("admin.rekap-penerimaan.get-data");
        $this->columnsUrl = route("admin.rekap-penerimaan.get-column");
    }

    public function index()
    {
        $data["title"] = $this->title;
        $data["mainTitle"] = $this->mainTitle;
        $data["columnsUrl"] = $this->columnsUrl;
        $data["datasUrl"] = $this->datasUrl;
        $data["post"] = mst_tagihan::select(["tagihan"])->get();
        $data["thn_aka"] = mst_thn_aka::select(["thn_aka"])
            ->where("thn_aka", "!=", null)
            ->orderBy("thn_aka", "desc")
            ->get();
        $data["kelas"] = mst_kelas::get();

        return view("admin.rekap_penerimaan.index", $data);
    }

    public  function getColumn(){

    }

    public  function getData(Request $request){
        $draw = $request->get("draw");
        if (
            $request->filter["periode"] != null &&
            preg_match(
                '/^\d{6}$/',
                $request->filter["periode"],
            )
        ) {
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get("order", []);
            $columnName_arr = $request->get("columns", []);
            $order_arr = $request->get("order", []);
            $search_arr = $request->get("search", []);
            $searchValue = $search_arr["value"] ?? "";

            $columnName = "scctcust.nmcust";
            $columnSortOrder = "DESC";

            if (!empty($order_arr)) {
                $columnIndex = $columnIndex_arr[0]["column"] ?? null;
                if (
                    $columnIndex !== null &&
                    !empty($columnName_arr[$columnIndex]["data"]) &&
                    $columnName_arr[$columnIndex]["data"] !== "no"
                ) {
                    $columnName = $columnName_arr[$columnIndex]["data"];
                    $columnSortOrder = $order_arr[0]["dir"] ?? "desc";
                }
            }

            $filters = [];
            $filterQuery = null;

            $filter = $request->input("filter");
            if ($filter) {
                foreach ($filter as $key => $val) {
                    if (
                        is_array($val) ||
                        (strtolower($val) != "all" &&
                            $val !== null &&
                            $val !== "")
                    ) {
                        $colName = match ($key) {
                            "tahun_akademik" => "scctbill.BTA",
                            "unit" => "scctcust.CODE01",
                            "kelas" => "scctcust.DESC02",
                            "siswa" => "scctcust.nmcust",
                            "custid" => "scctbill.CUSTID",
                            default => null,
                        };
                        if ($key == "tanggal-transaksi") {
                            if (
                                preg_match(
                                    '/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/',
                                    $val,
                                )
                            ) {
                                $val = preg_replace("/[-\/~]/", "-", $val);

                                [$startDate, $endDate] = explode(" - ", $val);
                                $startDate = Carbon::createFromFormat(
                                    "d-m-Y",
                                    $startDate,
                                )->startOfDay();
                                $endDate = Carbon::createFromFormat(
                                    "d-m-Y",
                                    $endDate,
                                )->endOfDay();
                                if ($startDate && $endDate) {
                                    $colName &&
                                    ($filters[] = [
                                        $colName,
                                        $startDate,
                                        $endDate,
                                        "whereBetween",
                                    ]);
                                }
                            }
                        } elseif ($key == "kelas") {
                            $val = explode("~", $val);
                            if (count($val) == 3) {
                                $filters[] = ["scctcust.CODE02", "=", $val[0]];
                                $filters[] = ["scctcust.DESC02", "=", $val[1]];
                                $filters[] = ["scctcust.DESC03", "=", $val[2]];
                            }
                        }  elseif ($key == "siswa") {
                            $val = is_numeric($val) ? $val : "%" . $val . "%";
                            $colName = is_numeric($val)
                                ? "scctcust.nocust"
                                : $colName;
                            $colName && ($filters[] = [$colName, "like", $val]);
                        } else {
                            $colName && ($filters[] = [$colName, "=", $val]);
                        }
                    }
                }

                if (!empty($filters)) {
                    $filterQuery = function ($query) use ($filters) {
                        foreach ($filters as $filter) {
                            switch (count($filter)) {
                                case 3:
                                    $filter[1] === "in"
                                        ? $query->whereIn(
                                        $filter[0],
                                        $filter[2],
                                    )
                                        : $query->where(
                                        $filter[0],
                                        $filter[1],
                                        $filter[2],
                                    );
                                    break;

                                case 4:
                                    $filter[3] === "whereBetween"
                                        ? $query->whereBetween($filter[0], [
                                        $filter[1],
                                        $filter[2],
                                    ])
                                        : $query->{$filter[3]}(
                                        $filter[0],
                                        $filter[1],
                                        $filter[2],
                                    );
                                    break;
                            }
                        }
                    };
                }
            }

            $whereAny = ["scctcust.nmcust", "scctcust.nocust"];

            $select = array_unique(
                array_merge($whereAny, [
                    "scctbill.AA",
                    "scctbill.BILLNM",
                    "scctbill.BILLAM",
                    "scctbill.PAIDST",
                    "scctbill.PAIDDT",
                    "scctbill.BTA",
                    "scctbill.CUSTID",
                    "scctbill.FIDBANK",
                    "scctbill.FUrutan",
                    "scctcust.CODE02",
                    "scctcust.DESC02",
                ]),
            );

            $query = scctcust::leftJoin(
                "sccttran",
                "scctcust.CUSTID",
                "sccttran.CUSTID",
            )
                ->where("scctbill.PAIDST", 1)
                ->where("scctbill.FSTSBolehBayar", 1)
                ->where("scctcust.STCUST", 1)
                ->where("scctbill.PAIDDT", "!=", null)
                ->whereAny($whereAny, "like", "%" . $searchValue . "%")
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                })->orderByRaw("
                    CASE
                        WHEN scctbill.BILLNM LIKE '%JULI%' THEN 1
                        WHEN scctbill.BILLNM LIKE '%AGUSTUS%' THEN 2
                        WHEN scctbill.BILLNM LIKE '%SEPTEMBER%' THEN 3
                        WHEN scctbill.BILLNM LIKE '%OKTOBER%' THEN 4
                        WHEN scctbill.BILLNM LIKE '%NOVEMBER%' THEN 5
                        WHEN scctbill.BILLNM LIKE '%DESEMBER%' THEN 6
                        WHEN scctbill.BILLNM LIKE '%JANUARI%' THEN 7
                        WHEN scctbill.BILLNM LIKE '%FEBRUARI%' THEN 8
                        WHEN scctbill.BILLNM LIKE '%MARET%' THEN 9
                        WHEN scctbill.BILLNM LIKE '%APRIL%' THEN 10
                        WHEN scctbill.BILLNM LIKE '%MEI%' THEN 11
                        WHEN scctbill.BILLNM LIKE '%JUNI%' THEN 12
                        ELSE 999
                    END
                ");

            // Total records
            $totalRecords = scctbill::select("count(*) as allcount")
                ->where("PAIDST", 1)
                ->where("scctbill.FSTSBolehBayar", 1)
                ->where("PAIDDT", "!=", null)
                ->count();

            $totalRecordswithFilter = (clone $query)->count();

            $rowperpage = $rowperpage == "poll" ? $totalRecords : $rowperpage;
            $records = (clone $query)
                ->orderBy($columnName, $columnSortOrder)
                ->select($select)
                ->whereAny($whereAny, "like", "%" . $searchValue . "%")
                ->skip($start)
                ->take($rowperpage)
                ->get();

            $records = $records->map(function ($item, $index) use ($request) {
                $item->NOVA = match (strtolower($item->CODE02)) {
                    "mts" => scctcust::showVAMTS($item->nocust),
                    "ma" => scctcust::showVAMA($item->nocust),
                    default => "",
                };

                $item->item_id = $item["AA"];

                return $item;
            });

            $records->toArray();
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords ?? 0,
            "recordsFiltered" => $totalRecordswithFilter ?? 0,
            "data" => $records ?? [],
        ];
        return response()->json($response);
    }
}
