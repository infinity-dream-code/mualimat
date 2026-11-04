<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetodeBayar;
use App\Models\mst_tagihan;
use App\Models\mst_thn_aka;
use App\Models\scctbill;
use App\Models\scctcust;
use App\Support\UnitScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CekPelunasanController extends Controller
{
    private string $title;
    private string $datasUrl;
    private string $columnsUrl;
    private string $mainTitle;
    private ?string $unitScope = null;

    public function __construct()
    {
        $this->title = 'Rekap Data';
        $this->mainTitle = 'Cek Pelunasan';
        $this->datasUrl = route('admin.cek-pelunasan.get-data');
        $this->detailDatasUrl = '';
        $this->columnsUrl = route('admin.cek-pelunasan.get-column');
        $this->middleware(function ($request, $next) {
            $this->unitScope = UnitScope::current();
            return $next($request);
        });
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['columnsUrl'] = $this->columnsUrl;
        $data['datasUrl'] = $this->datasUrl;
        $data['post'] = mst_tagihan::select(['tagihan'])->get();
        $data['thn_aka'] = mst_thn_aka::select(['thn_aka'])->where('thn_aka', '!=', null)->get();
        $data['periode'] = scctbill::select('BILLAC')
            ->whereNotNull('BILLAC')
            ->where('BILLAC', '!=', '')
            ->distinct()
            ->orderBy('BILLAC', 'desc')
            ->get();
        $data["metode_bayar"] = MetodeBayar::attributes();
        $data['kelas'] = UnitScope::kelasOptions($this->unitScope);
        $data['unit'] = UnitScope::sekolahOptions($this->unitScope);
        $data['unitScope'] = $this->unitScope;

        return view('admin.cek_pelunasan.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => 'AA', 'name' => 'no', 'columnType' => 'row'],
            ['data' => 'BTA', 'name' => 'Tahun Pelajaran', 'searchable' => true, 'orderable' => true],
            ['data' => 'NOCUST', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'NUM2ND', 'name' => 'No Pendaftaran', 'searchable' => true, 'orderable' => true],
            ['data' => 'NMCUST', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLNM', 'name' => 'Nama Tagihan', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLAC', 'name' => 'Periode', 'searchable' => true, 'orderable' => true, 'columnType' => 'periode'],
            ['data' => 'BILLAM', 'name' => 'Tagihan', 'searchable' => true, 'orderable' => true, 'columnType' => 'currency', 'classname' => 'text-end'],
            ['data' => 'PAIDST', 'name' => 'Lunas', 'searchable' => true, 'orderable' => true, 'columnType' => 'boolean', 'trueVal' => 'LUNAS', 'falseVal' => 'BELUM BAYAR'],
        ];
    }

    public function cetakKartuSiswa(Request $request)
    {
        if (!$request->filled('custid')) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 422);
        }

        $custid = $request->input('custid');
        $siswaQuery = scctcust::query()->where('CUSTID', $custid)->where('STCUST', 1);
        UnitScope::apply($siswaQuery, $this->unitScope, 'CODE01');
        $siswa = $siswaQuery->first();
        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 422);
        }

        try {
            // Query langsung per siswa — hindari getData (count/sum seluruh unit + LIMIT besar)
            $query = scctbill::query()
                ->where('CUSTID', $custid)
                ->where('FSTSBolehBayar', 1);

            $filter = $request->input('filter', []);
            if (!empty($filter['tahun_akademik']) && strtolower((string) $filter['tahun_akademik']) !== 'all') {
                $query->where('BTA', $filter['tahun_akademik']);
            }
            if (!empty($filter['status_bayar']) && strtolower((string) $filter['status_bayar']) !== 'all') {
                $query->where('PAIDST', $filter['status_bayar']);
            }
            if (!empty($filter['metode_bayar']) && strtolower((string) $filter['metode_bayar']) !== 'all') {
                $metode = $filter['metode_bayar'];
                if ($metode === 'NULL') {
                    $query->whereNull('FIDBANK');
                } elseif ($metode === 'empty') {
                    $query->where('FIDBANK', '');
                } else {
                    $query->where('FIDBANK', 'like', $metode);
                }
            }
            if (!empty($filter['post']) && is_array($filter['post']) && !in_array('all', $filter['post'], true)) {
                $posts = array_values(array_filter($filter['post'], fn ($v) => $v !== 'all' && $v !== null && $v !== ''));
                if ($posts !== []) {
                    $query->whereIn('BILLNM', $posts);
                }
            }
            if (!empty($filter['periode']) && is_array($filter['periode']) && !in_array('all', $filter['periode'], true)) {
                $periodes = array_values(array_filter($filter['periode'], fn ($v) => $v !== 'all' && $v !== null && $v !== ''));
                if ($periodes !== []) {
                    $query->whereIn('BILLAC', $periodes);
                }
            }

            $tagihans = $query
                ->orderBy('BILLAC', 'asc')
                ->orderBy('BILLNM', 'asc')
                ->get(['BTA', 'BILLNM', 'BILLAC', 'BILLAM', 'PAIDST'])
                ->map(function ($item) {
                    $row = $item->toArray();
                    $row['BILLAC'] = $item->BILLAC !== null && $item->BILLAC !== ''
                        ? (string) $item->BILLAC
                        : '-';
                    return $row;
                })
                ->values()
                ->all();

            if (empty($tagihans)) {
                return response()->json(['message' => 'Tagihan Tidak Ditemukan'], 422);
            }

            $nova = ($siswa->NOCUST && $siswa->NOCUST !== '-')
                ? scctcust::showVA($siswa->NOCUST)
                : (($siswa->NUM2ND && $siswa->NUM2ND !== '-')
                    ? scctcust::showVA($siswa->NUM2ND)
                    : null);

            $pdf = Pdf::loadView('cetak.kartu-siswa', [
                'tagihans' => $tagihans,
                'siswa' => $siswa,
                'nova' => $nova,
            ]);

            return $pdf->download('kartu-siswa.pdf');
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membuat kartu siswa',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order', []);
        $columnName_arr = $request->get('columns', []);
        $order_arr = $request->get('order', []);
        $search_arr = $request->get('search', []);
        $searchValue = $search_arr['value'] ?? '';

        $hasUserOrder = false;
        $columnName = 'scctcust.NOCUST';
        $columnSortOrder = 'asc';

        if (!empty($order_arr)) {
            $columnIndex = $columnIndex_arr[0]['column'] ?? null;
            if ($columnIndex !== null && !empty($columnName_arr[$columnIndex]['data']) && $columnName_arr[$columnIndex]['data'] !== 'no') {
                $rawColumn = $columnName_arr[$columnIndex]['data'];
                $columnName = match ($rawColumn) {
                    'NOCUST', 'NMCUST', 'NUM2ND' => 'scctcust.' . $rawColumn,
                    'BILLAC', 'BILLNM', 'BILLAM', 'BTA', 'PAIDST', 'PAIDDT', 'AA', 'FIDBANK', 'FUrutan', 'CUSTID' => 'scctbill.' . $rawColumn,
                    default => $rawColumn,
                };
                $columnSortOrder = $order_arr[0]['dir'] ?? 'desc';
                $hasUserOrder = true;
            }
        }

        $filters = [];
        $filterQuery = null;

        $filter = $request->input('filter');
        if ($filter) {
            foreach ($filter as $key => $val) {
                if (is_array($val) || strtolower($val) != 'all' && $val !== null && $val !== '') {
                    $colName = match ($key) {
                        "tahun_akademik" => "scctbill.BTA",
                        'post' => 'scctbill.BILLNM',
                        'periode' => 'scctbill.BILLAC',
                        "unit" => "scctcust.CODE01",
                        'kelas' => 'scctcust.DESC02',
                        'siswa' => 'scctcust.nmcust',
                        'custid' => 'scctbill.CUSTID',
                        'status_bayar' => 'scctbill.PAIDST',
                        'metode_bayar' => 'scctbill.FIDBANK',
                        default => null
                    };
                    switch ($key) {
                        case "metode_bayar":
                            if ($val === "NULL") {
                                $colName && ($filters[] = [$colName, "=", null]);
                            } else if ($val === "empty") {
                                $colName && ($filters[] = [$colName, "=", '']);
                            } else {
                                $colName && ($filters[] = [$colName, "like", "$val"]);
                            }
                            break;
                        case "kelas":
                            $val = explode("~", $val);
                            if (count($val) == 3) {
                                $filters[] = ["scctcust.CODE02", "=", $val[0]];
                                $filters[] = ["scctcust.DESC02", "=", $val[1]];
                                $filters[] = ["scctcust.DESC03", "=", $val[2]];
                            }
                            break;
                        case "post":
                        case "periode":
                            // "all"/Semua → tampil semua (abaikan pilihan lain)
                            if (is_array($val) && in_array("all", $val, true)) {
                                break;
                            }
                            $array = array_values(array_filter((array) $val, function ($value) {
                                return $value !== "all" && $value !== null && $value !== "";
                            }));
                            if (count($array) > 0) {
                                $colName &&
                                ($filters[] = [$colName, "in", $array]);
                            }
                            break;
                        case 'siswa':
                            $val = is_numeric($val) ? $val : "%" . $val . "%";
                            $colName = is_numeric($val)
                                ? "scctcust.nocust"
                                : $colName;
                            $colName && ($filters[] = [$colName, "like", $val]);
                            break;
                        default:
                            $colName && ($filters[] = [$colName, "=", $val]);
                            break;
                    }
                }
            };

            if (!empty($filters)) {
                $filterQuery = function ($query) use ($filters) {
                    foreach ($filters as $filter) {
                        switch (count($filter)) {
                            case 3:
                                $filter[1] === 'in'
                                    ? $query->whereIn($filter[0], $filter[2])
                                    : $query->where($filter[0], $filter[1], $filter[2]);
                                break;

                            case 4:
                                $filter[3] === 'whereBetween'
                                    ? $query->whereBetween($filter[0], [$filter[1], $filter[2]])
                                    : $query->{$filter[3]}($filter[0], $filter[1], $filter[2]);
                                break;
                        }
                    }
                };
            }
        }

        $whereAny = [
            'scctcust.NMCUST',
            'scctcust.NOCUST',
        ];

        $select = array_unique(array_merge($whereAny, [
            'scctbill.AA',
            'scctbill.BILLNM',
            'scctbill.BILLAC',
            'scctbill.BILLAM',
            'scctbill.PAIDST',
            'scctbill.PAIDDT',
            'scctbill.BTA',
            'scctbill.FIDBANK',
            'scctbill.FUrutan',
            'scctcust.CODE02',
            'scctcust.DESC02',
            'scctcust.NUM2ND',
            'scctbill.CUSTID',
        ]));

        $query = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
            ->where('scctbill.FSTSBolehBayar', 1)
            ->where('scctcust.STCUST', 1)
            ->whereAny($whereAny, 'like', '%' . $searchValue . '%')
            ->where(function ($query) use ($filterQuery) {
                if ($filterQuery) {
                    $filterQuery($query);
                }
            });
        UnitScope::apply($query, $this->unitScope);

        $totalRecordsQuery = scctbill::query()
            ->leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
            ->where('scctbill.FSTSBolehBayar', 1)
            ->where('scctcust.STCUST', 1);
        UnitScope::apply($totalRecordsQuery, $this->unitScope);
        $totalRecords = $totalRecordsQuery->count();

        $totalRecordswithFilter = (clone $query)->count();
        $totalTagihan = (float) ((clone $query)->sum('scctbill.BILLAM') ?? 0);

        $rowperpage = $rowperpage == "poll" ? $totalRecords : $rowperpage;
        $recordsQuery = (clone $query)->select($select)
            ->whereAny($whereAny, 'like', '%' . $searchValue . '%');

        // Default / cetak: NIS asc, periode asc. User column click overrides primary sort.
        if ($hasUserOrder) {
            $recordsQuery->orderBy($columnName, $columnSortOrder)
                ->orderBy('scctcust.NOCUST', 'asc')
                ->orderBy('scctbill.BILLAC', 'asc');
        } else {
            $recordsQuery->orderBy('scctcust.NOCUST', 'asc')
                ->orderBy('scctbill.BILLAC', 'asc');
        }

        $records = $recordsQuery
            ->skip($start)
            ->take($rowperpage)
            ->get();

        if ($request->get("length") != "poll") {
            $records = $records->map(function ($item, $index) {
                $item->item_id = $item['AA'];
                $item->CUSTID = $item['CUSTID'];
                $item->BILLAC = $item->BILLAC !== null && $item->BILLAC !== ''
                    ? (string) $item->BILLAC
                    : '-';
                return $item;
            });
        } else {
            $records = $records->map(function ($item) {
                $item->BILLAC = $item->BILLAC !== null && $item->BILLAC !== ''
                    ? (string) $item->BILLAC
                    : '-';
                return $item;
            });
        }

        $data = $records->map(fn ($item) => $item instanceof \Illuminate\Database\Eloquent\Model
            ? $item->toArray()
            : (array) $item
        )->values()->all();

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords ?? 0,
            "recordsFiltered" => $totalRecordswithFilter ?? 0,
            "data" => $data,
            "totals" => [
                "tagihan" => [
                    "location" => 7,
                    "value" => $totalTagihan,
                    "columnType" => "currency",
                ],
            ],
        ];
        return response()->json($response);
    }
}
