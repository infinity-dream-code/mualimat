@extends('layouts.export.kop_file')
@php use Carbon\Carbon; @endphp
@section('title', 'Rekap Pembayaran')
@section('content')
    <table width="100%">
        <tr>
            <td colspan="2" align="center">
                <h4>Rekap Tagihan</h4>
            </td>
        </tr>
    </table>
    <table width="100%" class="main-table">
        <tr>
            <td style="width: auto"
                class="border-right-0">Unit</td>
            <td class="border-left-0">
                : <strong>{{$kelas->unit}}</strong>
            </td>
        </tr>
        <tr>
            <td style="width: auto"
                class="border-right-0">Kelas</td>
            <td class="border-left-0">
                : <strong>{{$kelas->jenjang}} - {{$kelas->kelas}}</strong>
            </td>
        </tr>
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
        @php $totalTagihanSiswa = 0; @endphp
        @foreach($tagihans as $tagihan)
            @php $totalTagihanSiswaIni = 0; @endphp
            <tr>
                <td>{{$loop->index + 1}}</td>
                <td>{{$tagihan->nocust}}</td>
                <td>{{$tagihan->nmcust}}</td>
                @foreach($mstTagihan as $item)
                    <td class="text-end">@rupiah($tagihan[$item->tagihan])</td>
                    @php
                        $totalTagihanSiswaIni += isset($tagihan[$item->tagihan]) ? $tagihan[$item->tagihan] : 0;
                        $totalTagihanSiswa += $totalTagihanSiswaIni;
                    @endphp
                @endforeach
                <td>@rupiah($totalTagihanSiswaIni)</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot style="background-color: #e5e6e8;">
        <tr>
            <td colspan="3">Total</td>
            @foreach($mstTagihan as $item)
                <td class="text-end">@rupiah($tagihans->sum($item->tagihan))</td>
            @endforeach
            <td class="text-end">@rupiah($totalTagihanSiswa)</td>
        </tr>
        </tfoot>
    </table>
@endsection
