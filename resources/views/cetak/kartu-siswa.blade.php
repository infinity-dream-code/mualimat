@extends('layouts.export.kop_file')
@php use Carbon\Carbon; @endphp
@section('title', 'Bukti Pembayaran  ' . ($siswa->NOCUST ?? '') . ' - ' . ($siswa->NMCUST ?? ''))
@section('content')
    <table width="100%">
        <tr>
            <td colspan="2" align="center">
                <h4>KARTU TAGIHAN SISWA</h4>
            </td>
        </tr>
    </table>
    @php
        $nis = !($siswa->NOCUST === '' || is_null($siswa->NOCUST) || !is_numeric($siswa->NOCUST));
    @endphp
    <table width="100%" class="main-table">
        <tr>
            <td style="width: auto"
                class="border-right-0">{{$nis ?  'NIS' : 'No Daft' }}</td>
            <td class="border-left-0">
                :<strong> {{$nis ? $siswa->NOCUST : $siswa->NUM2ND }}</strong>
            </td>
            <td style="width: auto" class="border-right-0">Kelas</td>
            <td class="border-left-0">:<strong> {{$siswa->DESC02??''}} - {{$siswa->DESC03??''}}</strong></td>
        </tr>
        <tr>
            <td style="width: auto"
                class="border-right-0">NOVA
            </td>
            <td class="border-left-0">
                :<strong> {{$nova ?? ''}}</strong>
            </td>
            <td style="width: auto" class="border-right-0">Unit</td>
            <td class="border-left-0">:<strong> {{$siswa->CODE02??''}}</strong></td>
        </tr>
        <tr>
            <td class="border-right-0">Nama Siswa</td>
            <td class="border-left-0">:<strong> {{$siswa->NMCUST??''}} </strong></td>
            <td class="border-right-0">Angkatan</td>
            <td class="border-left-0">:<strong> {{$siswa->DESC04??''}}</strong></td>
        </tr>
    </table>
    <table width="100%" class="table-border main-table">
        <thead class="table-border" style="background-color: #ededed;">
        <tr>
            <th>#</th>
            <th>Tahun Akademik</th>
            <th>Nama Tagihan</th>
            <th>Periode</th>
            <th>Jumlah</th>
            <th>Tanggal Bayar</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @php
            $paid = 0;
            $unpaid = 0;
            $bulanNama = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
        @endphp
        @foreach($tagihans as $tagihan)
            @php
                $BILLNM = $tagihan['BILLNM'];
                $nextBILLNM =  false;
                $beforeBILLNM =  false;
                $BILLNMClass = '';
                if (count($tagihans) > 1){
                    if ($loop->index > 0 && $loop->index < (count($tagihans) - 1)){
                        if($tagihans[$loop->index + 1]['BILLNM'] == $tagihan['BILLNM']) $nextBILLNM = true;
                        if($tagihans[$loop->index - 1]['BILLNM'] == $tagihan['BILLNM']) $beforeBILLNM = true;
                    }elseif ($loop->index ==0){
                        if($tagihans[$loop->index + 1]['BILLNM'] == $tagihan['BILLNM']) $nextBILLNM = true;
                    }elseif ($loop->index == (count($tagihans) - 1)){
                        if($tagihans[$loop->index - 1]['BILLNM'] == $tagihan['BILLNM']) $beforeBILLNM = true;
                    }
                }
//
                $BILLNMClass = !$nextBILLNM ? '' : ' border-bottom-0';
                $BILLNMClass .= !$beforeBILLNM?'':' border-top-0';

                if($nextBILLNM && $beforeBILLNM) $BILLNM = '';
                if($beforeBILLNM) $BILLNM = '';

                $rawPeriode = trim((string) ($tagihan['BILLAC'] ?? ''));
                if ($rawPeriode === '' || $rawPeriode === '-') {
                    $periodeLabel = '-';
                } elseif (preg_match('/^\d{4}$/', $rawPeriode)) {
                    $periodeLabel = $rawPeriode;
                } elseif (preg_match('/^\d{6}$/', $rawPeriode)) {
                    $tahun = (int) substr($rawPeriode, 0, 4);
                    $bulan = (int) substr($rawPeriode, 4, 2);
                    $periodeLabel = ($bulan >= 1 && $bulan <= 12)
                        ? ($bulanNama[$bulan] . ' ' . $tahun)
                        : $rawPeriode;
                } else {
                    $periodeLabel = $rawPeriode;
                }

                $rawPaidDt = $tagihan['PAIDDT'] ?? null;
                if (blank($rawPaidDt) || $rawPaidDt === '0000-00-00' || $rawPaidDt === '0000-00-00 00:00:00') {
                    $tanggalBayarLabel = '-';
                } else {
                    try {
                        $tanggalBayarLabel = Carbon::parse($rawPaidDt)->format('d-m-Y');
                    } catch (\Throwable $e) {
                        $tanggalBayarLabel = '-';
                    }
                }

                if ($tagihan['PAIDST'] == 0) {
                    $unpaid += $tagihan['BILLAM'];
                } else{
                    $paid += $tagihan['BILLAM'];
                }
            @endphp
            <tr>
                <th scope="row">{{$loop->index + 1}}</th>
                <td>{{$tagihan['BTA']}}</td>
                <td class="{{$BILLNMClass}}">{{$BILLNM}}</td>
                <td>{{$periodeLabel}}</td>
                <td align="right">@rupiah($tagihan['BILLAM'])</td>
                <td align="center">{{$tanggalBayarLabel}}</td>
                <td align="center">{!! $tagihan['PAIDST'] == 0 ? '<span style="color:red;">BELUM LUNAS</span>': '<span style="color:green;">LUNAS</span>'!!}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot style="background-color: #ededed; font-weight: bold;">
        <tr>
            <td colspan="4">Total Tagihan</td>
            <td align="right">@rupiah($paid + $unpaid)</td>
            <td colspan="2" rowspan="3"></td>
        </tr>
        <tr>
            <td colspan="4">Total Tagihan Terbayar</td>
            <td align="right">@rupiah($paid)</td>
        </tr>
        <tr>
            <td colspan="4">Total Sisa Tagihan</td>
            <td align="right">@rupiah($unpaid)</td>
        </tr>
        </tfoot>
    </table>
@endsection
