<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\mst_kelas;
use App\Models\mst_tagihan;
use App\Models\mst_thn_aka;
use App\Models\scctbill;
use App\Models\scctcust;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DataPenerimaanController extends Controller
{
    public function __construct()
    {
        $this->title = 'Data Penerimaan';

        $this->datasUrl = route('admin.data-penerimaan.get-data');
        $this->detailDatasUrl = '';
        $this->columnsUrl = route('admin.data-penerimaan.get-column');
    }

    public function getColumn()
    {
        return [
            ['data' => 'AA', 'name' => 'no', 'columnType' => 'row'],
//            ['data' => 'item_id', 'name' => 'ITEM ID', 'visible' => false],
            ['data' => 'nocust', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'nmcust', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'CODE02', 'name' => 'Unit', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC02', 'name' => 'Kelas', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLNM', 'name' => 'Nama Tagihan', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLAM', 'name' => 'Tagihan', 'searchable' => true, 'orderable' => true, 'columnType' => 'currency', 'className' => 'text-end'],
            ['data' => 'FIDBANK', 'name' => 'Metode', 'columnType' => 'custom_code_tagihan', 'searchable' => true, 'orderable' => true],
            ['data' => 'PAIDDT', 'name' => 'Tanggal Bayar', 'columnType' => 'timestamp', 'searchable' => true, 'orderable' => true],
            ['data' => 'BTA', 'name' => 'Tahun AKA', 'searchable' => true, 'orderable' => true],
        ];
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['columnsUrl'] = $this->columnsUrl;
        $data['datasUrl'] = $this->datasUrl;
        $data['post'] = mst_tagihan::select(['tagihan'])->get();
        $data['thn_aka'] = mst_thn_aka::select(['thn_aka'])->where('thn_aka', '!=', null)->get();
        $data['kelas'] = mst_kelas::get();

        return view('admin.data_penerimaan', $data);
    }

    public function cetak(Request $request)
    {
        ini_set('max_execution_time', 300);
//        $pdf = Pdf::loadView('cetak.data-penerimaan')->setPaper('a4', 'landscape');
//        return $pdf->download('rekap-tagihan.pdf');

        try {
            $filters = [];
            $filterQuery = null;

            $filter = $request->input('filter');
            if ($filter) {
                if ($request->filter['tanggal-transaksi'] != null && preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $request->filter['tanggal-transaksi'])) {
                    foreach ($filter as $key => $val) {
                        if (strtolower($val) != 'all' && $val !== null && $val !== '') {
                            $colName = match ($key) {
                                'tanggal-transaksi' => 'scctbill.PAIDDT',
                                'tahun_akademik' => 'scctbill.BTA',
                                'post' => 'scctbill.BILLNM',
                                'kelas' => 'scctcust.DESC02',
                                'siswa' => 'scctcust.nmcust',
                                default => null
                            };
                            if ($key == 'tanggal-transaksi') {
                                if (preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $val)) {
                                    $val = preg_replace('/[-\/~]/', '-', $val);

                                    list($startDate, $endDate) = explode(' - ', $val);
                                    $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
                                    $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
                                    if ($startDate && $endDate) {
                                        ($colName) && $filters[] = [$colName, $startDate, $endDate, 'whereBetween'];
                                    }
                                }
                            } elseif ($key == 'siswa') {
                                $val = is_numeric($val) ? $val : '%' . $val . '%';
                                $colName = is_numeric($val) ? 'scctcust.nocust' : $colName;
                                ($colName) && $filters[] = [$colName, 'like', $val];
                            } else {
                                ($colName) && $filters[] = [$colName, '=', $val];
                            }
                        }
                    }

                    if (!empty($filters)) {
                        $filterQuery = function ($query) use ($filters) {
                            foreach ($filters as $filter) {
                                if (count($filter) === 3) {
                                    $query->where($filter[0], $filter[1], $filter[2]);
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
                                ->where('scctbill.PAIDST', 1)
                                ->where('scctbill.FSTSBolehBayar', 1)
                                ->where('scctcust.STCUST', 1)
                                ->where('scctbill.PAIDDT', '!=', null)
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
                } else {
                    return response()->json(['message' => 'Tanggal transaksi tidak valid'], 422);
                }
            }

//            dd($posts[0]['tagihan']);
//            return  view('pdf.data_penerimaan.rekap_penerimaan', ['posts' => $posts]);

//            $view = view('cetak.data-penerimaan', compact('posts'))->render();
//            return response()->json(['html' => $view]);

            if ($posts) {
                $pdf = Pdf::loadView('cetak.data-penerimaan', ['posts' => $posts])->setPaper('a4', 'landscape');
                return $pdf->download('rekap-tagihan.pdf');
            } else {
                return response()->json(['message' => 'Data Kosong'], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Tidak dapat mencetak rekap', 'error' => $e->getMessage(), 'e' => $e], 422);
        }
    }

    public function cetakPembayaran(Request $request)
    {
        try {
            $decryptedId = Crypt::decrypt($request->id_tagihan);
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Data tidak ditemukan'], 422);
        }

        $tagihans = scctbill::where('id', $decryptedId)->get();
        $siswa = mst_siswa::where('id', $tagihans[0]->CUSTID)->first();
        if ($siswa && $tagihans) {
            $siswa = $request->session()->get('siswa_tagihan_baru_dibayar');
            $pdf = Pdf::loadView('pdf.kuitansi', ['tagihans' => $tagihans, 'siswa' => $siswa]);
            return $pdf->download('bukti-pembayaran - ' . $siswa->nama . ' - ' . $siswa->nis . '.pdf');
        } else {
            return response()->json(['message' => 'Silakhan Lakukan pembayaran terlebih dahulu'], 422);
        }
    }

    public function cetakKartuSiswa(Request $request)
    {
        if (!$request['custid']) return response()->json(['error' => 'siswa tidak ditemukan']);
        $request['draw'] = 2;
        $request['start'] = 0;
        $request['length'] = "poll";
        try {
            $val = Crypt::decrypt($request['custid']);
        } catch (DecryptException $e) {
            return response()->json(['error' => 'siswa tidak ditemukan']);
        }

        $siswa = scctcust::where('custid', $val)->first();
        if (!$siswa) return response()->json(['error' => 'siswa tidak ditemukan']);

        $request->merge([
            'filter' => array_merge($request->input('filter', []), [
                'custid' => $val
            ])
        ]);

        $filter = $request;
        $tagihans = $this->getData($filter);

        try {
            $tagihans = json_decode(json_encode($tagihans), true);
            $tagihans = $tagihans['original']['data'];
            if (!$tagihans) return response()->json(['message' => 'Tagihan Tidak Ditemukan'], 422);
//            dd($tagihans, $siswa);
            $pdf = Pdf::loadView('cetak.kartu-siswa', ['tagihans' => $tagihans, 'siswa' => $siswa]);
            return $pdf->download('kartu-siswa.pdf');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Tagihan Tidak Ditemukan', 'error' => $e], 422);
        }
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        if ($request->filter['tanggal-transaksi'] != null && preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $request->filter['tanggal-transaksi'])) {

            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order', []);
            $columnName_arr = $request->get('columns', []);
            $order_arr = $request->get('order', []);
            $search_arr = $request->get('search', []);
            $searchValue = $search_arr['value'] ?? '';

            $columnName = 'BILLAC';
            $columnSortOrder = 'DESC';

            if (!empty($order_arr)) {
                $columnIndex = $columnIndex_arr[0]['column'] ?? null;
                if ($columnIndex !== null && !empty($columnName_arr[$columnIndex]['data']) && $columnName_arr[$columnIndex]['data'] !== 'no') {
                    $columnName = $columnName_arr[$columnIndex]['data'];
                    $columnSortOrder = $order_arr[0]['dir'] ?? 'desc';
                }
            }

            $filters = [];
            $filterQuery = null;

            $filter = $request->input('filter');
            if ($filter) {
                foreach ($filter as $key => $val) {
                    if (is_array($val) || strtolower($val) != 'all' && $val !== null && $val !== '') {
                        $colName = match ($key) {
                            'dari_tanggal', 'sampai_tanggal' => 'scctbill.FTGLTagihan',
                            'tanggal-transaksi' => 'scctbill.PAIDDT',
                            'tahun_akademik' => 'scctbill.BTA',
                            'post' => 'scctbill.BILLNM',
                            'kelas' => 'scctcust.DESC02',
                            'siswa' => 'scctcust.nmcust',
                            'custid' => 'scctbill.CUSTID',
                            default => null
                        };
                        if ($key == 'tanggal-transaksi') {
                            if (preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $val)) {
                                $val = preg_replace('/[-\/~]/', '-', $val);

                                list($startDate, $endDate) = explode(' - ', $val);
                                $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
                                $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
                                if ($startDate && $endDate) {
                                    ($colName) && $filters[] = [$colName, $startDate, $endDate, 'whereBetween'];
                                }
                            }
                        } else if ($key == 'kelas') {
                            $val = explode("~", $val);
                            if (count($val) == 3) {
                                $filters[] = ['scctcust.CODE02', '=', $val[0]];
                                $filters[] = ['scctcust.DESC02', '=', $val[1]];
                                $filters[] = ['scctcust.DESC03', '=', $val[2]];
                            }
                        } else if ($key == 'post') {
                            ($colName) && $filters[] = [$colName, 'in', $val];
                        } elseif ($key == 'siswa') {
                            $val = is_numeric($val) ? $val : '%' . $val . '%';
                            $colName = is_numeric($val) ? 'scctcust.nocust' : $colName;
                            ($colName) && $filters[] = [$colName, 'like', $val];
                        } else {
                            ($colName) && $filters[] = [$colName, '=', $val];
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
                'scctcust.nmcust',
                'scctcust.nocust',
            ];

            $select = array_unique(array_merge($whereAny, [
                'scctbill.AA',
                'scctbill.BILLNM',
                'scctbill.BILLAM',
                'scctbill.PAIDST',
                'scctbill.PAIDDT',
                'scctbill.BTA',
                'scctbill.CUSTID',
                'scctbill.FIDBANK',
                'scctbill.FUrutan',
                'scctcust.CODE02',
                'scctcust.DESC02',

            ]));

            $query = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
                ->where('scctbill.PAIDST', 1)
                ->where('scctbill.FSTSBolehBayar', 1)
                ->where('scctcust.STCUST', 1)
                ->where('scctbill.PAIDDT', '!=', null)
                ->whereAny($whereAny, 'like', '%' . $searchValue . '%')
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                });

            // Total records
            $totalRecords = scctbill::select('count(*) as allcount')
                ->where('PAIDST', 1)
                ->where('scctbill.FSTSBolehBayar', 1)
                ->where('PAIDDT', '!=', null)
                ->count();

            $totalRecordswithFilter = (clone $query)
                ->count();

            $rowperpage = $rowperpage == "poll" ? $totalRecords : $rowperpage;
            $records = (clone $query)->orderBy($columnName, $columnSortOrder)
                ->select($select)
                ->whereAny($whereAny, 'like', '%' . $searchValue . '%')
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                })
                ->skip($start)
                ->take($rowperpage)
                ->get()
                ->map(function ($item, $index) {
                    $item->item_id = crypt::encrypt($item->AA);
                    $item->CUSTID = crypt::encrypt($item->CUSTID);
                    return $item;
                })->toArray();
        }

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords ?? 0,
            "recordsFiltered" => $totalRecordswithFilter ?? 0,
            "data" => $records ?? [],
        );
        return response()->json($response);
    }

    public function cetakRekapPenerimaan(Request $request)
    {
        if ($request->filter['tanggal-transaksi'] != null
            && preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $request->filter['tanggal-transaksi'])
            && $request->filter['kelas'] != null && $request->filter['kelas'] != 'all') {
            $filters = [];
            $filterQuery = null;
            $filter_scctbill = [];
            $post = false;
            $kelas = [];
            $tanggalMulai = null;
            $tanggalSelesai = null;
            $filter = $request->input('filter');
            if ($filter) {
                foreach ($filter as $key => $val) {
                    if (is_array($val) || strtolower($val) != 'all' && $val !== null && $val !== '') {
                        $colName = match ($key) {
                            'dari_tanggal', 'sampai_tanggal' => 'scctbill.FTGLTagihan',
                            'tanggal-transaksi' => 'scctbill.PAIDDT',
                            'tahun_akademik' => 'scctbill.BTA',
                            'post' => 'scctbill.BILLNM',
                            'kelas' => 'scctcust.DESC02',
                            'siswa' => 'scctcust.nmcust',
                            'custid' => 'scctbill.CUSTID',
                            default => null
                        };

                        if ($key == 'tanggal-transaksi') {
                            if (preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $val)) {
                                $val = preg_replace('/[-\/~]/', '-', $val);
                                list($startDate, $endDate) = explode(' - ', $val);
                                $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
                                $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
//                                $tanggalMulai = $startDate->format('l, t F Y');
                                $tanggalMulai = $startDate->isoFormat('dddd, D MMMM YYYY');
                                $tanggalSelesai = $endDate->isoFormat('dddd, D MMMM YYYY');
                                if ($startDate && $endDate) {
                                    ($colName) && $filters[] = [$colName, $startDate, $endDate, 'whereBetween'];
                                }
                            }
                        } else if ($key == 'kelas') {
                            $val = explode("~", $val);
                            $kelas = $val;
                            if (count($val) == 3) {
                                $filters[] = ['scctcust.CODE02', '=', $val[0]];
                                $filters[] = ['scctcust.DESC02', '=', $val[1]];
                                $filters[] = ['scctcust.DESC03', '=', $val[2]];
                            }
                        } else if ($key == 'post') {
                            $post = $val;
                            ($colName) && $filters[] = [$colName, 'in', $val];
                        } else if ($key == 'siswa') {
                            $val = is_numeric($val) ? $val : '%' . $val . '%';
                            $colName = is_numeric($val) ? 'scctcust.nocust' : $colName;
                            ($colName) && $filters[] = [$colName, 'like', $val];
                        } else {
                            ($colName) && $filters[] = [$colName, '=', $val];
                        }
                    }
                };
            }

            $filter_main = [];
            $filter_scctbill = [];

            foreach ($filters as $item) {
                if (str_contains($item[0], "scctbill")) {
                    $filter_scctbill[] = $item;
                } else {
                    $filter_main[] = $item;
                }
            }

            $whereAny = [
                'scctcust.nmcust',
                'scctcust.nocust',
            ];

            $select = array_unique(array_merge($whereAny, [
                'scctcust.CODE02',
                'scctcust.DESC02',
                'scctcust.DESC03',

            ]));

            $mstTagihan = mst_tagihan::select(['tagihan'])
                ->where(function ($query) use ($post) {
                    if ($post) {
                        $query->whereIn('tagihan', $post);
                    }
                })
                ->orderBy('urut', 'asc')
                ->get();

            $records = scctcust::leftJoin('scctbill', function ($join) use ($filter_scctbill) {
                $join->on('scctbill.CUSTID', '=', 'scctcust.CUSTID')
                    ->where('scctbill.PAIDST', 1)
                    ->where('scctbill.FSTSBolehBayar', 1)
                    ->whereNotNull('scctbill.PAIDDT')
                    ->where(function ($query) use ($filter_scctbill) {
                        foreach ($filter_scctbill as $filter) {
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
                    });
            })->where(function ($query) use ($filter_main) {
                foreach ($filter_main as $filter) {
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
            })->where('scctcust.STCUST', 1)
                ->select($select)
                ->groupBy('scctcust.CUSTID');

            foreach ($mstTagihan as $val) {
                $namaPost = $val['tagihan'];
                $records->addSelect(DB::raw("SUM(CASE WHEN scctbill.BILLNM = '{$namaPost}' THEN scctbill.BILLAM ELSE 0 END) AS '{$namaPost}'"));
            }

            $records = $records->get();

            $kelas = mst_kelas::where('unit', $kelas[0])
                ->where('jenjang', $kelas[1])
                ->where('kelas', $kelas[2])
                ->first();
            if ($records && $mstTagihan) {

                $customPaper = [0, 0, 1684, 842];

                $pdf = Pdf::loadView('cetak.rekap-penerimaan',
                    [
                        'tagihans' => $records,
                        'mstTagihan' => $mstTagihan,
                        'kelas' => $kelas,
                        'tanggalMulai' => $tanggalMulai,
                        'tanggalSelesai' => $tanggalSelesai,
                    ])
                    ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isPhpEnabled' => true,
//                        'dpi' => 96,
                        ])
//                    ->setPaper('a4', 'landscape');
                    ->setPaper($customPaper);
                return $pdf->download('rekap-penerimaan.pdf');
            }
        }

        return response()->json(['message' => 'Gagal mencetak Rekap Penerimaan'], 422);

//        $sqlWithPlaceholders = $records->toSql();
//
//        $bindings = $records->getBindings();
//
//        $fullSql = Str::replaceArray('?', array_map(function ($b) {
//            return is_numeric($b) ? $b : "'" . addslashes($b) . "'";
//        }, $bindings), $sqlWithPlaceholders);

//        dd($records);

    }
}
