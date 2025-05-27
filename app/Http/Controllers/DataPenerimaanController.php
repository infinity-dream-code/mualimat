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

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");
        $columnName_arr = $request->get('columns');
        $search_arr = $request->get('search');

        $defaultColumn = 'scctbill.PAIDDT';
        $defaultOrder = 'desc';

        if ($request->filter['tanggal-transaksi'] != null && preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $request->filter['tanggal-transaksi'])) {
            if ($request->has('order')) {
                $columnIndex_arr = $request->get('order');
                $columnIndex = $columnIndex_arr[0]['column'];
                $columnSortOrder = $columnIndex_arr[0]['dir'];
            } else {
                $columnIndex = $defaultColumn;
                $columnSortOrder = $defaultOrder;
            }

            $columnName = $columnName_arr[$columnIndex]['data'];
            $searchValue = $search_arr['value'];

            if (!$columnName || $columnName == 'no') {
                $columnName = $defaultColumn;
                $columnSortOrder = $defaultOrder;
            }

            $filters = [];
            $filterQuery = null;

            $filter = $request->input('filter');
            if ($filter) {
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
                };

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
                    $item->print = true;
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
                }else{
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
}
