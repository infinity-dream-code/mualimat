@extends('layouts.export.kop_file')
@php use Carbon\Carbon; @endphp
@section('title', 'Rekap Pembayaran')
@section('content')
    <table width="100%">
        <tr>
            <td colspan="2" align="center">
                <h4>Rekap Pembayaran</h4>
            </td>
        </tr>
    </table>
    <table width="100%" class="table-border main-table">
        <thead class="table-border" style="background-color: #ededed;">
        <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Kelas</th>
            @foreach($mstTagihan as $item)
                <th>{{$item->tagihan}}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($tagihans as $tagihan)
            <tr>
                <td>{{$loop->index}}</td>
                <td>{{$tagihan->NMCUST}}</td>
                <td>{{$tagihan->DESC02.$tagihan->DESC03}}</td>
                @foreach($mstTagihan as $item)
                    <td>{{$tagihan[$item->tagihan]}}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
