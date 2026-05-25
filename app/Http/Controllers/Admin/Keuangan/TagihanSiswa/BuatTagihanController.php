<?php

namespace App\Http\Controllers\Admin\Keuangan\TagihanSiswa;

use App\Http\Controllers\Controller;
use App\Models\mst_kelas;
use App\Models\mst_tagihan;
use App\Models\mst_thn_aka;
use App\Models\u_akun;
use App\Models\u_daftar_harga;
use App\Models\scctbill;
use App\Models\scctbill_detail;
use App\Models\scctcust;
use App\Models\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuatTagihanController extends Controller
{
    private string $title = 'Keuangan';
    private string $mainTitle = 'Tagihan Siswa';
    private string $dataTitle = 'Buat Tagihan';
    private string $showTitle = 'Buat Tagihan';

    private ?string $unitScope = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $user = Auth::user();
                $this->unitScope = $user->unit;
            }
            return $next($request);
        });
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['showTitle'] = $this->showTitle;

        $data['thn_aka'] = mst_thn_aka::orderBy('thn_aka', 'desc')->get();
        $data['kelas'] = mst_kelas::when($this->unitScope, function ($query) {
            $query->where("unit", $this->unitScope);
        })
            ->orderByRaw("CASE WHEN kelas REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, kelas")
            ->get();
        $data['tagihan'] = mst_tagihan::orderBy('urut', 'asc')->get();

        return view('admin.keuangan.tagihan_siswa.buat_tagihan.index_new', $data);
    }

    public function getColumn()
    {
        return [
            [
                'data' => 'check',
                'name' => "<input type='checkbox' class='form-check-input' id='check-all'>",
                'className' => 'text-center',
                'input' => 'check',
                "targets" => 0
            ],
            ['data' => 'kode', 'name' => 'Kode', 'searchable' => true, 'orderable' => true],
            ['data' => 'nama_post', 'name' => 'Nama Post', 'searchable' => true, 'orderable' => true],
            ['data' => 'nama_tagihan', 'name' => 'Nama Tagihan', 'searchable' => true, 'orderable' => true],
            ['data' => 'nominal', 'name' => 'Nominal', 'input' => 'text', 'currency' => true],
            ['data' => 'potongan', 'name' => 'Potongan', 'input' => 'text', 'currency' => true],
            ['data' => 'cicilan', 'name' => 'Cicilan', 'input' => 'text', 'currency' => false],
        ];
    }

    public function getData(Request $request)
    {
        return response()->json([
            "draw" => intval($request->get('draw')),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
        ]);
    }

    public function getColumnSiswa()
    {
    }

    public function getDataSiswa(Request $request)
    {
    }

    public function getSiswa(Request $request)
    {
        $kelasId = $request->kelas != 'all' ? $request->kelas ?? null : null;
        $thn_aka = $request->angkatan != 'all' ? $request->angkatan ?? null : null;

        $nis = null;
        $nama = null;
        if (isset($request->cari_siswa) && $request->cari_siswa) {
            is_numeric($request->cari_siswa)
                ? $nis = '%' . $request->cari_siswa . '%'
                : $nama = '%' . $request->cari_siswa . '%';
        }

        $select = [
            'scctcust.CUSTID',
            'scctcust.nocust as nis',
            'scctcust.NUM2ND as nomor_pendaftaran',
            'scctcust.nmcust as nama',
            'scctcust.CODE02',
            'scctcust.DESC02',
            'scctcust.DESC03',
            'scctcust.DESC04 as angkatan',
        ];

        $kelasModel = $kelasId ? mst_kelas::where('id', $kelasId)->first() : null;

        $query = scctcust::query()
            ->when($this->unitScope, fn ($q) => $q->where('scctcust.CODE02', $this->unitScope));

        if ($request->siswa_only == true) {
            $query->when($nis, function ($q, $nis) {
                return $q->where(function ($q2) use ($nis) {
                    $q2->where('scctcust.nocust', 'like', $nis)
                        ->orWhere('scctcust.NUM2ND', 'like', $nis);
                });
            })
            ->when($nama, fn ($q, $nama) => $q->where('scctcust.nmcust', 'like', $nama));
        } elseif ($kelasModel) {
            $query->where('scctcust.CODE03', '=', $kelasModel->id)
                ->when($thn_aka, fn ($q, $t) => $q->where('scctcust.DESC04', '=', $t))
                ->when($nis, fn ($q, $n) => $q->where('scctcust.nocust', 'like', $n))
                ->when($nama, fn ($q, $n) => $q->where('scctcust.nmcust', 'like', $n));
        } else {
            return response()->json(['data' => []]);
        }

        $siswa = $query->select($select)
            ->orderBy('scctcust.nocust', 'asc')
            ->limit(500)
            ->get()
            ->map(function ($item) {
                return [
                    'CUSTID' => $item->CUSTID,
                    'nis' => $item->nis,
                    'nomor_pendaftaran' => $item->NUM2ND,
                    'nama' => $item->nama,
                    'CODE02' => $item->CODE02,
                    'kelas' => trim(($item->DESC02 ?? '') . ' ' . ($item->DESC03 ?? '')),
                    'jenjang' => $item->DESC02,
                    'angkatan' => $item->angkatan,
                ];
            });

        return response()->json(['data' => $siswa]);
    }

    public function getMasterHarga(Request $request)
    {
        $data = [];
        $thn_aka = $request->thn_aka != 'all' ? $request->thn_aka ?? null : null;
        $kelas = $request->kelas != 'all' ? $request->kelas ?? null : null;

        $select = [
            'u_daftar_harga.thn_masuk as tahun_masuk',
            'u_daftar_harga.KodeAkun as kode_akun',
            'u_akun.NamaAkun as nama_akun',
            'u_daftar_harga.nominal as nominal',
        ];

        if ($thn_aka) {
            $data = u_akun::orderBy('u_akun.KodeAkun', 'asc')
                ->join('u_daftar_harga', 'u_daftar_harga.KodeAkun', '=', 'u_akun.KodeAkun')
                ->when($thn_aka, function ($query, $thn_aka) {
                    return $query->where('u_daftar_harga.thn_masuk', 'like', $thn_aka);
                })->when($kelas, function ($query, $kelas) {
                    return $query->where(function ($q) use ($kelas) {
                        $q->where('u_daftar_harga.kode_prod', 'like', $kelas)
                            ->orWhereNull('u_daftar_harga.kode_prod')
                            ->orWhere('u_daftar_harga.kode_prod', '=', '');
                    });
                })
                ->select($select)
                ->orderBy('u_daftar_harga.KodeAkun', 'asc')
                ->whereNotNull('u_daftar_harga.KodeAkun')
                ->get()
                ->toArray();
        }

        $response = array(
            "data" => $data,
        );

        return response()->json($response);
    }


    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelajaran' => ['required', 'regex:/^\d{4}\/\d{4}(?:\s*-\s*(GANJIL|GENAP))?$/'],
            'tahun_angkatan' => ['required', 'regex:/^\d{4}\/\d{4}(?:\s*-\s*(GANJIL|GENAP))?$/'],
            'kelas' => ['required'],
            'siswa' => ['required', 'array', 'min:1'],
            'fungsi' => ['required', 'regex:/^\d{6}$/'],
            'nama_tagihan' => ['required'],
            'tagihan' => ['required', 'array', 'min:1', 'max:1'],
            'tagihan.*.tagihan' => ['required'],
            'tagihan.*.nominal' => ['required', 'regex:/^[0-9]+(\.[0-9]{3})*$/', 'not_in:0'],
        ], ValidationMessage::messages(), ValidationMessage::attributes());

        $tahun_angkatan = mst_thn_aka::where('thn_aka', $request->tahun_angkatan)->value('thn_aka');
        if (!$tahun_angkatan) {
            return response()->json(['message' => 'Tahun angkatan tidak valid'], 422);
        }

        $tahun_pelajaran_val = mst_thn_aka::where('thn_aka', $request->tahun_pelajaran)->value('thn_aka');
        if (!$tahun_pelajaran_val || !preg_match('/\d{4}\/\d{4}/', $tahun_pelajaran_val, $matches)) {
            return response()->json(['message' => 'Tahun pelajaran tidak valid'], 422);
        }
        $tahun_pelajaran_bta = $matches[0];

        $tahun_pelajaran = $request->input('tahun_pelajaran');
        $kelas = $request->input('kelas');
        $tagihans = u_daftar_harga::leftJoin('u_akun', 'u_akun.KodeAkun', '=', 'u_daftar_harga.KodeAkun')
            ->whereIn('u_daftar_harga.KodeAkun', $request->input('tagihan.*.tagihan'))
            ->when($tahun_angkatan, function ($query, $tahun_angkatan) {
                return $query->where('u_daftar_harga.thn_masuk', 'like', $tahun_angkatan);
            })->when($kelas, function ($query, $kelas) {
                return $query->where(function ($q) use ($kelas) {
                    $q->orwhere('u_daftar_harga.kode_prod', 'like', $kelas)
                        ->orWhereNull('u_daftar_harga.kode_prod')
                        ->orWhere('u_daftar_harga.kode_prod', '=', '');
                });
            })->select('u_daftar_harga.KodeAkun')
            ->get();

        if ($tagihans->isEmpty()) return response()->json(['message' => 'Tagihan tidak ditemukan 1'], 422);
        if (count($request->input('tagihan')) != $tagihans->count()) return response()->json(['message' => 'Jumlah tagihan yang dipilih tidak sesuai dengan jumlah data, silahkan muat ulang halaman!'], 422);

        try {
            DB::beginTransaction();

            $siswas = scctcust::whereIn('CUSTID', $request->input('siswa'))->get();
            if ($siswas->isEmpty()) return response()->json(['message' => 'Siswa tidak ditemukan'], 422);
            if (count($request->input('siswa')) != $siswas->count()) return response()->json(['message' => 'Jumlah siswa yang dipilih tidak sesuai dengan jumlah data, silahkan muat ulang halaman!'], 422);


            foreach ($siswas as $siswa) {
                $tagihanSiswaTerbaru = scctbill::where('CUSTID', $siswa->CUSTID)
                    ->select('CUSTID', 'FUrutan', 'BILLAC', 'BILLCD')
                    ->orderBy('FUrutan', 'DESC')
                    ->first();

                $urut = $tagihanSiswaTerbaru ? $tagihanSiswaTerbaru['FUrutan'] + 1 : 1;
                $billCD = date('Y') . '/i' . date('m') . '-' . ($urut + 1);

                foreach ($request->input('tagihan') as $item) {
                    $nominal = str_replace('.', '', $item['nominal']);
                    if (!is_numeric($nominal)) {
                        return response()->json(['message' => 'Nominal tidak boleh kosong'], 422);
                    }

                    $post = $tagihans->firstWhere(['KodeAkun'], $item['tagihan']);
                    if (!$post) return response()->json(['message' => 'Post tidak ditemukan'], 422);

                    $mst_tag = mst_tagihan::where('tagihan', $request['nama_tagihan'])->first();
                    if (!$mst_tag) return response()->json(['message' => 'Kode post tidak ditemukan'], 422);

                    $tahun = substr($request->fungsi, 0, 4);
                    $bulan = substr($request->fungsi, 4, 2);

                    $bill = scctbill::firstOrCreate([
                        'CUSTID' => $siswa->CUSTID,
                        'BILLAC' => $request->fungsi,
                        'BILLCD' => $billCD,
                        'BILLNM' => $mst_tag->tagihan
                    ], [
                        'BILLAM' => 0,
                        'PAIDST' => 0,
                        'FUrutan' => $urut,
                        'FTGLTagihan' => now(),
                        'FSTSBolehBayar' => 1,
                        'BTA' => $tahun_pelajaran_bta,
                    ]);

                    $bill->increment('BILLAM', $nominal);

                    $billDetail = scctbill_detail::create([
                        'KodePost' => $post->KodeAkun,
                        'CUSTID' => $bill->CUSTID,
                        'BILLAM' => $bill->BILLAM,
                        'tahun' => $tahun,
                        'periode' => $bulan,
                        'BILLCD' => $billCD,
                    ]);
                }
            }
            DB::commit();
            return response()->json(['message' => 'Tagihan telah dibuat']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Data gagal dibuat', 'error' => $e], 422);
        }
    }
}
