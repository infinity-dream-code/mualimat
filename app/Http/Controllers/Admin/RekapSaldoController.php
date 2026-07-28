<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\mst_kelas;
use App\Models\mst_sekolah;
use App\Models\mst_thn_aka;
use App\Models\scctcust;
use App\Models\sccttran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RekapSaldoController extends Controller
{
    private string $title;
    private string $datasUrl;
    private string $columnsUrl;
    private string $mainTitle;

    public function __construct()
    {
        $this->title = "Saldo Per Periode";
        $this->mainTitle = "Saldo Per Periode";
        $this->datasUrl = route("admin.rekap-saldo.get-data");
        $this->columnsUrl = route("admin.rekap-saldo.get-column");
    }

    public function index()
    {
        $data["title"] = $this->title;
        $data["mainTitle"] = $this->mainTitle;
        $data["columnsUrl"] = $this->columnsUrl;
        $data["datasUrl"] = $this->datasUrl;
        $data["thn_aka"] = mst_thn_aka::select(["thn_aka"])
            ->where("thn_aka", "!=", null)
            ->orderBy("thn_aka", "desc")
            ->get();
        $data["kelas"] = mst_kelas::get();
        $data['unit'] = mst_sekolah::get();

        return view("admin.rekap_saldo.index", $data);
    }

    public function getColumn()
    {
        return [
            [
                "data" => null,
                "name" => "no",
                "columnType" => "row",
                "exportable" => true,
            ],
            [
                "data" => "nocust",
                "name" => "NIS",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
                "duplicate" => false
            ],
            [
                "data" => "nmcust",
                "name" => "NAMA",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
                "duplicate" => false
            ],
            [
                "data" => "CODE02",
                "name" => "Unit",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
                "duplicate" => false
            ],
            [
                "data" => "DESC02",
                "name" => "Kelas",
                "searchable" => true,
                "orderable" => true,
                "exportable" => true,
                "duplicate" => false
            ],
            [
                "data" => "opening_balance",
                "name" => "Saldo Awal",
                "exportable" => true,
                "columnType" => "currency",
            ],
            [
                "data" => "current_net",
                "name" => "Saldo Periode Dipilih",
                "exportable" => true,
                "columnType" => "currency",
            ],
            [
                "data" => "closing_balance",
                "name" => "Saldo Akhir",
                "exportable" => true,
                "columnType" => "currency",
            ],
        ];
    }

    public function getDataRekap(Request $request)
    {
        try {
            Log::info('=== getDataRekap START ===');
            Log::info('Request filter: ' . json_encode($request->filter));

            $periode = $request->filter["periode"] ?? null;
            $unit = $request->filter["unit"] ?? null;
            $kelas = $request->filter["kelas"] ?? null;

            Log::info('periode: ' . $periode);
            Log::info('unit: ' . $unit);
            Log::info('kelas: ' . $kelas);

            if (
                $periode == null ||
                !preg_match('/^\d{6}$/', $periode) ||
                !checkdate(substr($periode, 4, 2), 1, substr($periode, 0, 4))
            ) {
                Log::warning('Periode invalid: ' . $periode);
                return response()->json([
                    "error" => "Periode is not valid or defined!",
                    "message" => "Silahkan pilih periode terlebih dahulu"
                ], 422);
            }

            $kelas = strtolower(trim($kelas ?? ''));
            $unit  = strtolower(trim($unit ?? ''));

            Log::info('kelas after trim: ' . $kelas);
            Log::info('unit after trim: ' . $unit);

            if (($kelas === '' || $kelas === 'all') && ($unit === '' || $unit === 'all')) {
                Log::warning('Filter tidak valid - unit dan kelas all');
                return response()->json([
                    "error" => "Filter tidak valid!",
                    "message" => "Silahkan pilih satu Unit atau satu kelas!"
                ], 422);
            }

            $request["draw"] = 2;
            $request["start"] = 0;
            $request["length"] = "poll";

            $filter = $request;
            $saldos = $this->getData($filter);

            Log::info('getData response: ' . json_encode($saldos));

            $saldos = json_decode(json_encode($saldos), true);
            $saldos = $saldos["original"]["data"] ?? [];

            Log::info('saldos count: ' . count($saldos));

            if (!$saldos) {
                Log::warning('Data saldo kosong');
                return response()->json(
                    ["message" => "Data saldo kosong"],
                    422,
                );
            }

            return response()->json([
                "data" => $saldos,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error in getDataRekap: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json(
                [
                    "message" => "Data Saldo Tidak Ditemukan",
                    "error" => $e->getMessage(),
                ],
                422,
            );
        }
    }

    public function getData(Request $request)
    {
        try {
            Log::info('=== getData START ===');
            Log::info('Request filter: ' . json_encode($request->filter));

            $draw = $request->get("draw");
            $records = [];
            $totalRecords = 0;
            $totalRecordswithFilter = 0;

            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get("order", []);
            $columnName_arr = $request->get("columns", []);
            $order_arr = $request->get("order", []);
            $search_arr = $request->get("search", []);
            $searchValue = $search_arr["value"] ?? "";

            $columnName = "NMCUST";
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

            // Mapping column untuk sorting
            $columnMap = [
                "nocust" => "NOCUST",
                "nmcust" => "NMCUST",
                "CODE02" => "CODE02",
                "DESC02" => "DESC02",
                "opening_balance" => "opening_balance",
                "current_net" => "current_net",
                "closing_balance" => "closing_balance",
            ];

            if (isset($columnMap[$columnName])) {
                $columnName = $columnMap[$columnName];
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
                            "unit" => "CODE01",
                            "kelas" => "DESC02",
                            "siswa" => "NMCUST",
                            default => null,
                        };
                        if ($key == "kelas") {
                            $val = explode("~", $val);
                            if (count($val) == 3) {
                                $filters[] = ["CODE02", "=", $val[0]];
                                $filters[] = ["DESC02", "=", $val[1]];
                                $filters[] = ["DESC03", "=", $val[2]];
                            }
                        } elseif ($key == "siswa") {
                            $val = is_numeric($val) ? $val : "%" . $val . "%";
                            $colName = is_numeric($val)
                                ? "NOCUST"
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
                                        : $query->where($filter[0], $filter[1], $filter[2]);
                                    break;
                            }
                        }
                    };
                }
            }

            $whereAny = ["NMCUST", "NOCUST"];

            // Query dari view v_saldo_va
            $query = DB::table('v_saldo_va')
                ->where('STCUST', 1)
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                });

            if (!blank($searchValue)) {
                $query->where(function ($q) use ($whereAny, $searchValue) {
                    $sanitizeSearch = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $searchValue);
                    foreach ($whereAny as $column) {
                        $q->orWhere($column, 'like', '%' . $sanitizeSearch . '%');
                    }
                });
            }

            $totalRecords = DB::table('v_saldo_va')
                ->where('STCUST', 1)
                ->count();

            $totalRecordswithFilter = (clone $query)->count();

            $rowperpage = $rowperpage == "poll" ? $totalRecords : $rowperpage;

            $records = (clone $query)
                ->orderBy($columnName, $columnSortOrder)
                ->skip($start)
                ->take($rowperpage)
                ->get();

            $records = $records->map(function ($item, $index) {
                // Mapping kolom dari view ke format yang diharapkan DataTable
                $item->nocust = $item->NOCUST ?? '';
                $item->nmcust = $item->NMCUST ?? '';
                $item->CODE02 = $item->CODE02 ?? '';
                $item->DESC02 = $item->DESC02 ?? '';
                $item->DESC03 = $item->DESC03 ?? '';
                $item->STCUST = $item->STCUST ?? 0;
                $item->opening_balance = 0;
                $item->current_net = $item->SALDO ?? 0;
                $item->closing_balance = $item->SALDO ?? 0;
                $item->item_id = $item->CUSTID;

                return $item;
            });

            $records = $records->toArray();

            $response = [
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords ?? 0,
                "recordsFiltered" => $totalRecordswithFilter ?? 0,
                "data" => $records ?? [],
            ];

            Log::info('response: ' . json_encode($response));
            Log::info('=== getData END ===');

            return response()->json($response);
        } catch (\Throwable $e) {
            Log::error('Error in getData: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                "draw" => intval($request->get("draw", 1)),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
