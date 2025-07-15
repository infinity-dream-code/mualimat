@extends('layouts.export.kop_file')
@php use Carbon\Carbon; @endphp
@section('title', 'Rekap Pembayaran')
@section('content')
    <table width="100%">
        <tr>
            <td colspan="2" align="center">
                <h4>Rekap Penerimaan Pembayaran</h4>
            </td>
        </tr>
    </table>
    <table width="100%" class="main-table">
        <tr>
            <td style="width: auto" class="border-right-0">
                Tanggal Transaksi
            </td>
            <td class="border-left-0">
                : <strong>{{$tanggalMulai}} - {{$tanggalSelesai}}</strong>
            </td>
        </tr>
        @isset($unit)
            <tr>
                <td style="width: auto"
                    class="border-right-0">Unit
                </td>
                <td class="border-left-0">
                    : <strong>{{$unit->DESC01}}</strong>
                </td>
            </tr>
        @endisset
        @isset($kelas)
            <tr>
                <td style="width: auto"
                    class="border-right-0">Kelas
                </td>
                <td class="border-left-0">
                    : <strong>{{$kelas[1]?? ''}} - {{$kelas[2]?? ''}}</strong>
                </td>
            </tr>
        @endisset
    </table>
    <table width="100%" class="table-border main-table">
        <thead style="background-color: #e5e6e8;">
        <tr>
            <th>#</th>
            <th>Nis</th>
            <th>Nama</th>
            @foreach($mstTagihan as $item)
                <th>{{$item->tagihan}}</th>
            @endforeach
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @php $totalPenerimaanSiswa = 0; @endphp
        @foreach($tagihans as $siswa)
            @php $totalPenerimaanSiswaIni = 0; @endphp
            <tr>
                <td>{{$loop->index + 1}}</td>
                <td>{{$siswa->nocust}}</td>
                <td>{{$siswa->nmcust}}</td>
                @foreach($mstTagihan as $item)
                    @php
                        $value = $siswa->{$item->tagihan} ?? 0;
                        $totalPenerimaanSiswaIni += $value;
                    @endphp
                    <td class="text-end">
                        @rupiah($value)
                    </td>
                @endforeach
                <td>@rupiah($totalPenerimaanSiswaIni)</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot style="background-color: #e5e6e8;">
        <tr>
            <td colspan="3">Total</td>
            @foreach($mstTagihan as $item)
                @php
                    $currentTotalPeneriemaan = $tagihans->sum($item->tagihan);
                    $totalPenerimaanSiswa += $currentTotalPeneriemaan;
                @endphp

                <td class="text-end">@rupiah($currentTotalPeneriemaan)</td>
            @endforeach
            <td class="text-end">@rupiah($totalPenerimaanSiswa)</td>
        </tr>
        </tfoot>
    </table>
@endsection
