<?php

namespace App\Http\Controllers\Admin\Keuangan\TagihanSiswa;

use App\Http\Controllers\Controller;
use App\Models\mst_kelas;
use App\Models\mst_sekolah;
use App\Models\mst_tagihan;
use App\Models\mst_thn_aka;
use App\Models\scctbill;
use App\Models\scctcust;
use App\Models\User;
use App\Models\ValidationMessage;
use App\Support\CacheHandler;
use App\Support\FilterHandler;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DataTagihanController extends Controller
{
    public ?string $sekolah = null;
    private string $title = "Keuangan";
    private string $mainTitle = 'Tagihan Siswa';
    private string $dataTitle = 'Data Tagihan Siswa';
    private string $cacheKey = 'data_tagihan';
    private array $allowedFilters = [
        'tanggal-pembuatan' => 'scctbill.FTGLTagihan',
        'tahun_akademik' => 'scctbill.BTA',
        'post' => 'scctbill.BILLNM',
        'kelas' => 'scctcust.DESC02',
        'sekolah' => 'scctcust.CODE02',
        'siswa' => 'scctcust.nmcust',
        'custid' => 'scctcust.CUSTID',
    ];

    public function __construct()
    {
        $key = Str::slug($this->cacheKey) . '_cache_version';

        Cache::add($key, 1);
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $user = Auth::user();
                $this->sekolah = $user->sekolah;
            }
            return $next($request);
        });
    }

    public function getColumn()
    {
        return [
            ['data' => 'AA', 'name' => 'no', 'columnType' => 'row', 'exportable' => true],
            ['data' => 'NOCUST', 'name' => 'NIS', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            ['data' => 'NUM2ND', 'name' => 'NO DAFT', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            ['data' => 'NOVA', 'name' => 'NO VA', 'exportable' => true],
            ['data' => 'NMCUST', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            ['data' => 'CODE02', 'name' => 'Unit', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            ['data' => 'DESC02', 'name' => 'Kelas', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            ['data' => 'DESC03', 'name' => 'Kelompok', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            ['data' => 'BILLNM', 'name' => 'Nama Tagihan', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            ['data' => 'BILLAM', 'name' => 'Tagihan', 'searchable' => true, 'orderable' => true, 'columnType' => 'currency', 'className' => 'text-end', 'exportable' => true],
            ['data' => 'BTA', 'name' => 'Tahun AKA', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            ['data' => 'FUrutan', 'name' => 'Urutan', 'searchable' => true, 'orderable' => true, 'exportable' => true],
            [
                'data' => 'naik',
                'name' => 'Naik',
                'dataVal' => false,
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'action',
                'buttonText' => 'Naikkan',
                'buttonClass' => 'btn btn-sm btn-secondary btn-naik-urut',
                'buttonLink' => '#modal-delete',
                'buttonIcon' => 'ri-arrow-up-line me-2',
                'exportable' => false,
            ],
            [
                'data' => 'turun',
                'name' => 'Turun',
                'dataVal' => false,
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'action',
                'buttonText' => 'Turunkan',
                'buttonClass' => 'btn btn-sm btn-secondary btn-turun-urut',
                'buttonLink' => '#modal-delete',
                'buttonIcon' => 'ri-arrow-down-line me-2',
                'exportable' => false,
            ],
            [
                'data' => 'delete',
                'name' => '',
                'dataVal' => false,
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'action',
                'buttonText' => 'Hapus',
                'buttonClass' => 'btn btn-sm btn-danger btn-hapus',
                'buttonLink' => '#modal-delete',
                'buttonIcon' => 'ri-delete-bin-line me-2'
            ],
        ];
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['columnsUrl'] = $this->columnsUrl();
        $data['datasUrl'] = $this->datasUrl();
        $data['post'] = mst_tagihan::select(['tagihan'])->get();
        $data['thn_aka'] = mst_thn_aka::select(['thn_aka'])
            ->where('thn_aka', '!=', null)
            ->orderBy('thn_aka', 'desc')->get();
        $data['sekolah'] = mst_sekolah::when($this->sekolah, function ($query) {
            $query->where(function ($q) {
                $q->where("CODE01", $this->sekolah)
                    ->orWhere("DESC01", $this->sekolah);
            });
        })->get();
        $data['kelas'] = mst_kelas::when($this->sekolah, function ($query) {
            $query->where("unit", $this->sekolah);
        })->orderByRaw("CASE WHEN kelas REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, kelas")->get();
        $data['tanda_tangan'] = User::getTandaTanganBase64();

        return view('admin.keuangan.tagihan_siswa.data_tagihan', $data);
    }

    private function columnsUrl(): string
    {
        return route('admin.keuangan.tagihan-siswa.data-tagihan.get-column');
    }

    private function datasUrl(): string
    {
        return route('admin.keuangan.tagihan-siswa.data-tagihan.get-data');
    }

    public function ubahUrutan($id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'urutan_tagihan' => ['required', 'in:naik,turun'],
            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes()
        );

        if ($validator->fails()) {
            if ($validator->errors()->has('tagihan.nominal_bayar.*') || $validator->errors()->has('tagihan.post.*')) {
                return response()->json(['message' => 'Silahkan cek tagihan yang anda pilih,<br> pastikan telah mengisi nominal pembayaran'], 422);
            } else {
                return response()->json(['message' => $validator->errors()->first(), 'error' => $validator->errors()], 422);
            }
        }

        $tagihan = scctbill::where('AA', $id)
            ->where('FSTSBolehBayar', '=', 1)
            ->where('PAIDST', '=', 0)
            ->first();
        if (!$tagihan) return response()->json(['message' => 'Tagihan tidak ditemukan!'], 422);

        $siswa = scctcust::where('CUSTID', $request->input('custid'))->first();
        if (!$siswa) return response()->json(['message' => 'Siswa tidak ditemukan!'], 422);

        try {
            DB::beginTransaction();
            $custid = $request->input('custid');

            $bills = scctbill::query()
                ->where('CUSTID', $custid)
                ->where('PAIDST', 0)
                ->where('FSTSBolehBayar', 1)
                ->orderBy('FUrutan', 'asc')
                ->orderBy('AA', 'asc')
                ->get();

            $currentIndex = $bills->search(fn ($bill) => (int) $bill->AA === (int) $tagihan->AA);
            if ($currentIndex === false) {
                DB::rollBack();
                return response()->json(['message' => 'Tagihan tidak ditemukan pada daftar urutan siswa!'], 422);
            }

            if ($request->urutan_tagihan === 'naik') {
                if ($currentIndex <= 0) {
                    DB::rollBack();
                    return response()->json(['message' => 'Tagihan sudah berada pada urutan paling atas.'], 422);
                }
                $other = $bills[$currentIndex - 1];
            } else {
                if ($currentIndex >= $bills->count() - 1) {
                    DB::rollBack();
                    return response()->json(['message' => 'Tagihan sudah berada pada urutan paling bawah.'], 422);
                }
                $other = $bills[$currentIndex + 1];
            }

            $currentUrut = $tagihan->FUrutan;
            $tagihan->FUrutan = $other->FUrutan;
            $other->FUrutan = $currentUrut;
            $tagihan->save();
            $other->save();

            DB::commit();
            return response()->json(['message' => 'Urutan tagihan diubah!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal Mengubah Urutan Tagihan!, ' . $e->getMessage(), 'error' => $e->getMessage()], 422);
        }
    }

    public function cetak(Request $request)
    {
        ini_set('max_execution_time', 300);
//        $pdf = Pdf::loadView('cetak.data-penerimaan')->setPaper('a4', 'landscape');
//        return $pdf->download('rekap-tagihan.pdf');

        try {
            $filters = [];
            $filterQuery = null;

            $filter = FilterHandler::resolveFilters($request->input('filter'), $this->allowedFilters);
            if (!is_array($filter)) {
                $filter = [];
            }

            // Multi-select Nama Tagihan dikirim sebagai filter[post][].
            $rawPosts = $request->input('filter.post', []);
            if (!is_array($rawPosts) && !is_null($rawPosts) && $rawPosts !== '') {
                $rawPosts = [$rawPosts];
            }
            if (is_array($rawPosts)) {
                $postValues = array_values(array_filter($rawPosts, fn($item) => !is_null($item) && $item !== '' && strtolower((string) $item) !== 'all'));
                if (!empty($postValues)) {
                    $filter['scctbill.BILLNM'] = $postValues;
                }
            }
            if ($filter) {
                foreach ($filter as $key => $val) {
                    switch ($key) {
                        case 'scctbill.FTGLTagihan':
                            if (preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $val)) {
                                $val = preg_replace('/[-\/~]/', '-', $val);

                                list($startDate, $endDate) = explode(' - ', $val);
                                $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
                                $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
                                if ($startDate && $endDate) {
                                    ($key) && $filters[] = [$key, $startDate, $endDate, 'whereBetween'];
                                }
                            }
                            break;
                        case 'scctcust.nmcust':
                            $val = is_numeric($val) ? $val : '%' . $val . '%';
                            $colName = is_numeric($val) ? 'scctcust.nocust' : $key;
                            ($colName) && $filters[] = [$colName, 'like', $val];
                            break;
                        case 'scctbill.BILLNM':
                            if (is_array($val)) {
                                $postValues = array_values(array_filter($val, fn($item) => !is_null($item) && $item !== '' && strtolower((string) $item) !== 'all'));
                                if (!empty($postValues)) {
                                    $filters[] = ['scctbill.BILLNM', 'in', $postValues];
                                }
                            } else {
                                ($key) && $filters[] = [$key, '=', $val];
                            }
                            break;
                        case 'scctbill.BTA':
                            $normalizedVal = str_replace([' ', '-'], ['', '/'], trim((string) $val));
                            $filters[] = [
                                'whereRaw',
                                '(REPLACE(REPLACE(TRIM(scctbill.BTA), " ", ""), "-", "/") = ? OR REPLACE(REPLACE(TRIM(scctcust.DESC04), " ", ""), "-", "/") = ?)',
                                [$normalizedVal, $normalizedVal]
                            ];
                            break;
                        default:
                            ($key) && $filters[] = [$key, '=', $val];
                            break;
                        case 'scctcust.DESC02':
                            $delimiter = str_contains($val, '~~') ? '~~' : ',';
                            $val = explode($delimiter, $val);
                            if (count($val) == 3) {
                                if (!$this->sekolah) {
                                    $filters[] = [
                                        "scctcust.CODE02",
                                        "=",
                                        $val[0],
                                    ];
                                }
                                $filters[] = ['scctcust.DESC02', '=', $val[1]];
                                $filters[] = ['scctcust.DESC03', '=', $val[2]];
                            }
                            break;
                        case 'scctcust.CODE02':
                            $filters[] = ["scctcust.CODE02", "=", $val];
                            break;
                    }
                };

                if ($this->sekolah !== null && $this->sekolah !== '') {
                    $filters[] = ['scctcust.CODE02', '=', $this->sekolah];
                }

                if (!empty($filters)) {
                    $filterQuery = function ($query) use ($filters) {
                        foreach ($filters as $filter) {
                            if (($filter[0] ?? null) === 'whereRaw') {
                                $query->whereRaw($filter[1], $filter[2] ?? []);
                                continue;
                            }
                            if (count($filter) === 3) {
                                if (($filter[1] ?? null) === 'in' && is_array($filter[2] ?? null)) {
                                    $query->whereIn($filter[0], $filter[2]);
                                } else {
                                    $query->where($filter[0], $filter[1], $filter[2]);
                                }
                            } elseif (count($filter) === 4) {
                                if ($filter[3] == 'whereBetween') {
                                    $query->whereBetween($filter[0], [$filter[1], $filter[2]]);
                                } else {
                                    $query->{$filter[3]}($filter[0], $filter[1], $filter[2]);
                                }
                            }
                        }
                    };
                }


                $posts = mst_tagihan::select('urut', 'tagihan', 'kode')->get()
                    ->map(function ($item) use ($filterQuery) {
                        $item->tagihans = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
                            ->select([
                                'scctcust.nmcust',
                                'scctcust.nocust',
                                'scctbill.AA',
                                'scctbill.BILLNM',
                                'scctbill.BILLAM',
                                'scctbill.PAIDST',
                                'scctbill.PAIDDT',
                                'scctbill.BTA',
                                'scctbill.FIDBANK',
                                'scctbill.FUrutan',
                                'scctcust.CODE02',
                                'scctcust.DESC02',
                            ])
                            ->where('scctbill.BILLNM', $item->tagihan)
                            ->where('scctbill.PAIDST', 0)
                            ->where(function ($query) use ($filterQuery) {
                                if ($filterQuery) {
                                    $filterQuery($query);
                                }
                            })
                            ->orderBy('scctbill.CUSTID', 'desc')
                            ->orderBy('scctbill.BTA', 'desc')
                            ->orderBy('scctbill.PAIDDT', 'desc')
                            ->get()
                            ->toArray();;

                        return $item;
                    });

            }
            if ($posts) {
                $pdf = Pdf::loadView('cetak.data-tagihan', ['posts' => $posts])->setPaper('a4', 'landscape');
                return $pdf->download('rekap-tagihan.pdf');
            } else {
                return response()->json(['message' => 'Data Kosong'], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Tidak dapat mencetak rekap', 'error' => $e->getMessage(), 'e' => $e], 422);
        }
    }

    public function cetakKartuSiswa(Request $request)
    {
        $filter = $request;
        if (!$filter['custid']) return response()->json(['error' => 'Siswa tidak ditemukan']);
        $filter['draw'] = 2;
        $filter['start'] = 0;
        $filter['length'] = "poll";

        $siswa = scctcust::where('custid', $filter['custid'])->first();
        if (!$siswa) return response()->json(['error' => 'Siswa tidak ditemukan']);

        $request->merge([
            'filter' => array_merge($request->input('filter', []), [
                'custid' => $filter['custid']
            ])
        ]);

        $filter = $request;
        $tagihans = $this->getData($filter);

        try {
            $tagihans = json_decode(json_encode($tagihans), true);
            $tagihans = $tagihans['original']['data'];
            if (!$tagihans) return response()->json(['message' => 'Tagihan Tidak Ditemukan'], 422);
            return response()->json(['tagihans' => $tagihans, 'siswa' => $siswa], 200);
//            $pdf = Pdf::loadView('pdf.data_tagihan.kartu-siswa', ['tagihans' => $tagihans, 'siswa' => $siswa, 'tanda_tangan' => $tanda_tangan]);
//            return $pdf->download('kartu-siswa.pdf');
        } catch (\Dompdf\Exception $e) {
            return response()->json(['message' => 'Tagihan Tidak Ditemukan', 'error' => $e], 422);
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

        $columnName = "scctbill.FUrutan";
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

        $sortableColumns = [
            'BILLNM' => 'scctbill.BILLNM',
            'BILLAM' => 'scctbill.BILLAM',
            'BILLAC' => 'scctbill.BILLAC',
            'BTA' => 'scctbill.BTA',
            'FUrutan' => 'scctbill.FUrutan',
            'PAIDDT' => 'scctbill.PAIDDT',
            'NOCUST' => 'scctcust.NOCUST',
            'NMCUST' => 'scctcust.NMCUST',
            'DESC02' => 'scctcust.DESC02',
            'DESC03' => 'scctcust.DESC03',
        ];
        if (isset($sortableColumns[$columnName])) {
            $columnName = $sortableColumns[$columnName];
        } elseif ($columnName && !str_contains($columnName, '.')) {
            $columnName = 'scctbill.' . $columnName;
        }

        $filters = [];
        $filterQuery = null;

        $filter = FilterHandler::resolveFilters($request->input('filter'), $this->allowedFilters);
        if (!is_array($filter)) {
            $filter = [];
        }

        // Multi-select Nama Tagihan dikirim sebagai filter[post][].
        $rawPosts = $request->input('filter.post', []);
        if (!is_array($rawPosts) && !is_null($rawPosts) && $rawPosts !== '') {
            $rawPosts = [$rawPosts];
        }
        if (is_array($rawPosts)) {
            $postValues = array_values(array_filter($rawPosts, fn($item) => !is_null($item) && $item !== '' && strtolower((string) $item) !== 'all'));
            if (!empty($postValues)) {
                $filter['scctbill.BILLNM'] = $postValues;
            }
        }

        if ($this->sekolah !== null) {
            $filter = array_merge($filter, [
                'scctcust.CODE02' => $this->sekolah,
            ]);
        }

        if ($filter) {
            foreach ($filter as $key => $val) {
                switch ($key) {
                    case 'scctbill.FTGLTagihan':
                        if (preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $val)) {
                            $val = preg_replace('/[-\/~]/', '-', $val);

                            list($startDate, $endDate) = explode(' - ', $val);
                            $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
                            $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
                            if ($startDate && $endDate) {
                                ($key) && $filters[] = [$key, $startDate, $endDate, 'whereBetween'];
                            }
                        }
                        break;
                    case 'scctcust.nmcust':
                        $val = is_numeric($val) ? $val : '%' . $val . '%';
                        $colName = is_numeric($val) ? 'scctcust.nocust' : $key;
                        ($colName) && $filters[] = [$colName, 'like', $val];
                        break;
                    case 'scctbill.BILLNM':
                        if (is_array($val)) {
                            $postValues = array_values(array_filter($val, fn($item) => !is_null($item) && $item !== '' && strtolower((string) $item) !== 'all'));
                            if (!empty($postValues)) {
                                $filters[] = ['scctbill.BILLNM', 'in', $postValues];
                            }
                        } else {
                            ($key) && $filters[] = [$key, '=', $val];
                        }
                        break;
                    case 'scctbill.BTA':
                        $normalizedVal = str_replace([' ', '-'], ['', '/'], trim((string) $val));
                        $filters[] = [
                            'whereRaw',
                            '(REPLACE(REPLACE(TRIM(scctbill.BTA), " ", ""), "-", "/") = ? OR REPLACE(REPLACE(TRIM(scctcust.DESC04), " ", ""), "-", "/") = ?)',
                            [$normalizedVal, $normalizedVal]
                        ];
                        break;
                    case 'scctcust.DESC02':
                        $val = explode("~~", $val);
                        if (count($val) == 3) {
                            $filters[] = ['scctcust.CODE02', '=', $val[0]];
                            $filters[] = ['scctcust.DESC02', '=', $val[1]];
                            $filters[] = ['scctcust.DESC03', '=', $val[2]];
                        }
                        break;
                    case 'scctcust.CODE02':
                        $filters[] = ["scctcust.CODE02", "=", $val];
                        break;
                    default:
                        ($key) && $filters[] = [$key, '=', $val];
                        break;
                }
            };

            if (!empty($filters)) {
                $filterQuery = function ($query) use ($filters) {
                    foreach ($filters as $filter) {
                        if (($filter[0] ?? null) === 'whereRaw') {
                            $query->whereRaw($filter[1], $filter[2] ?? []);
                            continue;
                        }
                        if (count($filter) === 3) {
                            if (($filter[1] ?? null) === 'in' && is_array($filter[2] ?? null)) {
                                $query->whereIn($filter[0], $filter[2]);
                            } else {
                                $query->where($filter[0], $filter[1], $filter[2]);
                            }
                        } elseif (count($filter) === 4) {
                            if ($filter[3] == 'whereBetween') {
                                $query->whereBetween($filter[0], [$filter[1], $filter[2]]);
                            } else {
                                $query->{$filter[3]}($filter[0], $filter[1], $filter[2]);
                            }
                        }
                    }
                };
            }
        }

        $whereAny = [
            'scctcust.NMCUST',
            'scctcust.NOCUST',
            'scctcust.DESC02',
            'scctcust.DESC03',
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
            'scctcust.CODE02',
            'scctcust.NUM2ND',
            'scctbill.CUSTID',

        ]));

        $query = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
            ->select($select)
            ->selectRaw('CAST(COALESCE(scctbill.FUrutan, 0) AS UNSIGNED) AS FUrutan')
            ->where('scctbill.PAIDST', 0)
            ->where('scctbill.FSTSBolehBayar', 1)
            ->when(!blank($searchValue), function ($query) use ($whereAny, $searchValue) {
                $query->where(function ($q) use ($whereAny, $searchValue) {
                    $sanitizeSearch = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $searchValue);
                    foreach ($whereAny as $column) {
                        $q->orWhere($column, 'like', '%' . $sanitizeSearch . '%');
                    }
                });
            })
            ->where(function ($query) use ($filterQuery) {
                if ($filterQuery) {
                    $filterQuery($query);
                }
            });

        $totalRecords = $this->total();

        $totalRecordswithFilter = Cache::remember(
            CacheHandler::cacheKey($this->cacheKey, 'total_records_with_filter', $filter, $searchValue),
            now()->addMinutes(10),
            fn() => (clone $query)->count()
        );

        $cacheKey = CacheHandler::cacheKey($this->cacheKey, 'sum_tagihan', $filter, $searchValue);

        $totalTagihan =
            Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                fn() => (clone $query)->sum('BILLAM')
            );

        $rowperpage = $rowperpage == "poll" ? $totalRecords : $rowperpage;
        $records = (clone $query)
            ->orderBy('scctbill.BTA')
            ->orderByRaw("
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
                ")
            ->orderBy($columnName, $columnSortOrder)
            ->orderBy('scctcust.NOCUST', 'asc')
            ->orderBy('scctbill.FUrutan', 'asc')
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(function ($item, $index) {
                $item->item_id = $item['AA'];
                $item->NOVA = ($item->NOCUST && $item->NOCUST != '-') ? scctcust::showVA($item->NOCUST) : null;
                if (!$item->NOCUST || $item->NOCUST == '-') $item->NOCUST = null;
                if (!$item->NUM2ND || $item->NUM2ND == '-') $item->NUM2ND = null;
                $furutan = $item->FUrutan ?? $item->getAttribute('furutan');
                $item->FUrutan = (string) (int) ($furutan ?? 0);
                $item->print = true;
                $item->naik = true;
                $item->turun = true;
                $item->delete = true;
                return $item;
            })->toArray();
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords ?? 0,
            "recordsFiltered" => $totalRecordswithFilter ?? 0,
            "data" => $records ?? [],
            'totals' => [
                'tagihan' => ['location' => 8, 'value' => $totalTagihan, 'columnType' => 'currency'],
            ]
        );
        return response()->json($response);
    }

    public function total(): int
    {
        return Cache::remember(
            "{$this->cacheKey}:total_all_data",
            now()->addMinutes(10),
            fn() => scctbill::where('scctbill.PAIDST', 0)
                ->where('scctbill.FSTSBolehBayar', 1)
                ->count()
        );
    }
}
