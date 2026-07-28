<?php

namespace App\Http\Controllers\Admin\Rekap\RekapTaighan;

use App\Http\Controllers\Controller;
use App\Models\mst_kelas;
use App\Models\mst_sekolah;
use App\Models\mst_tagihan;
use App\Models\mst_thn_aka;
use App\Models\scctbill;
use App\Models\scctcust;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapTagihanPerAkunController extends Controller
{
    private string $title;
    private string $datasUrl;
    private string $columnsUrl;
    private string $mainTitle;

    public function __construct()
    {
        $this->title = "Rekap Data";
        $this->mainTitle = "Rekap Tagihan";
        $this->datasUrl = route("admin.rekap-tagihan.get-data");
        $this->columnsUrl = route("admin.rekap-tagihan.get-column");
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
        $data["unit"] = mst_sekolah::get();

        return view("admin.rekap_tagihan.index", $data);
    }

    public function getColumn()
    {
        return [
            ["data" => "AA", "name" => "no", "columnType" => "row"],
            [
                "data" => "BTA",
                "name" => "Tahun Pelajaran",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
            ],
            [
                "data" => "nocust",
                "name" => "NIS",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
            ],
            [
                "data" => "nmcust",
                "name" => "NAMA",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
            ],
            [
                "data" => "BILLNM",
                "name" => "Nama Tagihan",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
            ],
            [
                "data" => "FUrutan",
                "name" => "Urutan Tagihan",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
                "defaultContent" => "0",
            ],
            [
                "data" => "BILLAM",
                "name" => "Tagihan",
                "searchable" => true,
                "orderable" => true,
                "columnType" => "currency",
                "classname" => "text-end",
                "exportable" => true,
            ],
            [
                "data" => "FIDBANK",
                "name" => "Metode",
                "columnType" => "custom_code_tagihan",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
            ],
            [
                "data" => "FTGLTagihan",
                "name" => "Tanggal Buat Tagihan",
                "columnType" => "date",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
            ],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get("draw");
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get("order", []);
        $columnName_arr = $request->get("columns", []);
        $order_arr = $request->get("order", []);
        $search_arr = $request->get("search", []);
        $searchValue = $search_arr["value"] ?? "";

        $columnName = "scctcust.nmcust";
        $columnSortOrder = "asc";

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
                    (strtolower($val) != "all" && $val !== null && $val !== "")
                ) {
                    $colName = match ($key) {
                        "tahun_akademik" => "scctbill.BTA",
                        "post" => "scctbill.BILLNM",
                        "unit" => "scctcust.CODE01",
                        "kelas" => "scctcust.DESC02",
                        "siswa" => "scctcust.nmcust",
                        "custid" => "scctbill.CUSTID",
                        default => null,
                    };
                    if ($key == "kelas") {
                        $val = explode("~", $val);
                        if (count($val) == 3) {
                            $filters[] = ["scctcust.CODE02", "=", $val[0]];
                            $filters[] = ["scctcust.DESC02", "=", $val[1]];
                            $filters[] = ["scctcust.DESC03", "=", $val[2]];
                        }
                    } elseif ($key == "post") {
                        $array = array_filter($val, function ($value) {
                            return $value !== "all";
                        });
                        if (count($array) > 0) {
                            $colName && ($filters[] = [$colName, "in", $array]);
                        }
                    } elseif ($key == "siswa") {
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
                                    ? $query->whereIn($filter[0], $filter[2])
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

        // DB::raw() dipisah dari array_unique() karena array_unique mencoba
        // meng-cast tiap elemen ke string, dan objek Expression tidak bisa di-cast.
        $select = array_unique(
            array_merge($whereAny, [
                "scctbill.AA",
                "scctbill.BILLNM",
                "scctbill.BILLAM",
                "scctbill.PAIDST",
                "scctbill.FTGLTagihan",
                "scctbill.BTA",
                "scctbill.CUSTID",
                "scctbill.FIDBANK",
                "scctcust.CODE02",
                "scctcust.DESC02",
            ]),
        );
        $select[] = DB::raw("COALESCE(scctbill.FUrutan, 0) as FUrutan");

        try {
            $query = scctbill::leftJoin(
                "scctcust",
                "scctcust.CUSTID",
                "scctbill.CUSTID",
            )
                ->where("scctbill.PAIDST", 0)
                ->where("scctbill.FSTSBolehBayar", 1)
                ->where("scctcust.STCUST", 1)
                ->where(function ($query) use ($whereAny, $searchValue) {
                    foreach ($whereAny as $col) {
                        $query->orWhere($col, "like", "%" . $searchValue . "%");
                    }
                })
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                });

            // Total records
            $totalRecords = scctbill::select("count(*) as allcount")
                ->where("PAIDST", 0)
                ->where("scctbill.FSTSBolehBayar", 1)
                ->count();

            $totalRecordswithFilter = (clone $query)->count();

            $rowperpage = $rowperpage == "poll" ? $totalRecords : $rowperpage;

            // Urut berdasarkan nama customer, lalu urutan tagihan (FUrutan) ascending
            $records = (clone $query)
                ->reorder()
                ->orderBy('scctcust.nmcust', 'asc')
                ->orderBy('scctbill.FUrutan', 'asc')
                ->select($select)
                ->where(function ($query) use ($whereAny, $searchValue) {
                    foreach ($whereAny as $col) {
                        $query->orWhere($col, "like", "%" . $searchValue . "%");
                    }
                })
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

            $response = [
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords ?? 0,
                "recordsFiltered" => $totalRecordswithFilter ?? 0,
                "data" => $records ?? [],
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("RekapTagihan getData error: " . $e->getMessage(), [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return response()->json(
                [
                    "draw" => intval($draw),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => [],
                    "message" => "Gagal memuat data rekap tagihan",
                    "error" => $e->getMessage(),
                    "error_file" => $e->getFile() . ":" . $e->getLine(),
                ],
                500,
            );
        }
    }

    public function getRekapDataTagihan(Request $request)
    {
        $request->validate(
            [
                "filter.tahun_akademik" => "required|not_in:all",
            ],
            [
                "filter.tahun_akademik.required" =>
                "Silahkan pilih satu tahun akademik terlebih dahulu",
                "filter.tahun_akademik.not_in" =>
                "silahkan pilih satu tahun akademik terlebih dahulu",
            ],
        );

        $filters = [];
        $filter_scctbill = [];
        $post = false;
        $kelas = [];
        $unit = false;
        $filter = $request->input("filter");

        if ($filter) {
            foreach ($filter as $key => $val) {
                if (
                    is_array($val) ||
                    (strtolower($val) != "all" && $val !== null && $val !== "")
                ) {
                    $colName = match ($key) {
                        "dari_tanggal",
                        "sampai_tanggal" => "scctbill.FTGLTagihan",
                        "tanggal-transaksi" => "scctbill.PAIDDT",
                        "tahun_akademik" => "scctbill.BTA",
                        "post" => "scctbill.BILLNM",
                        "unit" => "scctcust.CODE01",
                        "kelas" => "scctcust.DESC02",
                        "siswa" => "scctcust.nmcust",
                        "custid" => "scctbill.CUSTID",
                        default => null,
                    };

                    if ($key == "kelas") {
                        $val = explode("~", $val);
                        $kelas = $val;
                        if (count($val) == 3) {
                            $filters[] = ["scctcust.CODE02", "=", $val[0]];
                            $filters[] = ["scctcust.DESC02", "=", $val[1]];
                            $filters[] = ["scctcust.DESC03", "=", $val[2]];
                        }
                    } elseif ($key == "post") {
                        $array = array_filter($val, function ($value) {
                            return $value !== "all";
                        });
                        if (count($array) > 0) {
                            $colName && ($filters[] = [$colName, "in", $array]);
                        }
                        $post = $array;
                    } elseif ($key === "unit") {
                        $unit = mst_sekolah::where("CODE01", $val)->first();
                        $colName && ($filters[] = [$colName, "=", $val]);
                    } elseif ($key == "siswa") {
                        $val = is_numeric($val) ? $val : "%" . $val . "%";
                        $colName = is_numeric($val)
                            ? "scctcust.nocust"
                            : $colName;
                        $colName && ($filters[] = [$colName, "like", $val]);
                    } elseif ($key == "tanggal-transaksi") {
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
                    } else {
                        $colName && ($filters[] = [$colName, "=", $val]);
                    }
                }
            }
        }

        $filter_main = [];

        foreach ($filters as $item) {
            if (str_contains($item[0], "scctbill")) {
                $filter_scctbill[] = $item;
            } else {
                $filter_main[] = $item;
            }
        }

        // DB::raw() dipisah dari array_unique() karena array_unique mencoba
        // meng-cast tiap elemen ke string, dan objek Expression tidak bisa di-cast.
        $select = array_unique([
            "scctbill.AA",
            "scctbill.BILLNM",
            "scctbill.BILLAM",
            "scctbill.PAIDST",
            "scctbill.FTGLTagihan",
            "scctbill.BTA",
            "scctbill.CUSTID",
            "scctbill.FIDBANK",
            "scctcust.nmcust",
            "scctcust.nocust",
            "scctcust.CODE02",
            "scctcust.DESC02",
            "scctcust.DESC03",
        ]);
        $select[] = DB::raw("COALESCE(scctbill.FUrutan, 0) as FUrutan");

        try {
            $records = scctbill::leftJoin(
                "scctcust",
                "scctcust.CUSTID",
                "scctbill.CUSTID",
            )
                ->where("scctbill.PAIDST", 0)
                ->where("scctbill.FSTSBolehBayar", 1)
                ->where("scctcust.STCUST", 1)
                ->where(function ($query) use ($filter_scctbill) {
                    foreach ($filter_scctbill as $filter) {
                        switch (count($filter)) {
                            case 3:
                                $filter[1] === "in"
                                    ? $query->whereIn($filter[0], $filter[2])
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
                })
                ->where(function ($query) use ($filter_main) {
                    foreach ($filter_main as $filter) {
                        switch (count($filter)) {
                            case 3:
                                $filter[1] === "in"
                                    ? $query->whereIn($filter[0], $filter[2])
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
                })
                // Urut berdasarkan nama customer, lalu urutan tagihan (FUrutan) ascending
                ->orderBy('scctcust.nmcust', 'asc')
                ->orderBy('scctbill.FUrutan', 'asc')
                ->select($select)
                ->get();

            if (!$records || $records->isEmpty()) {
                return response()->json([
                    "data" => [],
                    "kelas" => $kelas,
                    "message" => "Data tagihan tidak ditemukan"
                ], 200);
            }

            return response()->json([
                "data" => $records,
                "kelas" => $kelas,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(
                [
                    "message" =>
                    "Tidak dapat mencetak rekap tagihan!<br> *Silahkan hubungi administrator",
                    "error" => $e->getMessage(),
                ],
                422,
            );
        }
    }
}
