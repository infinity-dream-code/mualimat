@extends('layouts.export.kop_file')
@php use Carbon\Carbon; @endphp
@section('title', 'Kartu Tagihan ' . ($siswa->nocust ?? '') . ' - ' . ($siswa->nmcust ?? ''))
@section('content')
    <table width="100%">
        <tr>
            <td colspan="2" align="center">
                <h4>KARTU TAGIHAN SISWA</h4>
            </td>
        </tr>
    </table>
    @php
        $nis = !($siswa->nocust === '' || is_null($siswa->nocust) || !is_numeric($siswa->nocust));
    @endphp
    <table width="100%" class="main-table">
        <tr>
            <td style="width: auto" class="border-right-0">{{ $nis ? 'NIS' : 'No Daft' }}</td>
            <td class="border-left-0">
                :<strong> {{ $nis ? $siswa->nocust : ($siswa->NUM2ND ?? '-') }}</strong>
            </td>
            <td style="width: auto" class="border-right-0">Kelas</td>
            <td class="border-left-0">:<strong> {{ $siswa->DESC02 ?? '' }} - {{ $siswa->DESC03 ?? '' }}</strong></td>
        </tr>
        <tr>
            <td style="width: auto" class="border-right-0">NOVA</td>
            <td class="border-left-0">
                :<strong> {{ $nova ?? '' }}</strong>
            </td>
            <td style="width: auto" class="border-right-0">Unit</td>
            <td class="border-left-0">:<strong> {{ $siswa->CODE02 ?? '' }}</strong></td>
        </tr>
        <tr>
            <td class="border-right-0">Nama Siswa</td>
            <td class="border-left-0">:<strong> {{ $siswa->nmcust ?? '' }} </strong></td>
            <td class="border-right-0">Angkatan</td>
            <td class="border-left-0">:<strong> {{ $siswa->DESC04 ?? '' }}</strong></td>
        </tr>
    </table>
    <table width="100%" class="table-border main-table">
        <thead class="table-border" style="background-color: #ededed;">
        <tr>
            <th>#</th>
            <th>Tahun Akademik</th>
            <th>Nama Tagihan</th>
            <th>Jumlah</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse($tagihans as $tagihan)
            <tr>
                <th scope="row">{{ $loop->index + 1 }}</th>
                <td>{{ $tagihan->BTA ?? '-' }}</td>
                <td>{{ $tagihan->BILLNM ?? '-' }}</td>
                <td align="right">@rupiah($tagihan->BILLAM ?? 0)</td>
                <td align="center">
                    {!! ($tagihan->PAIDST ?? 0) == 0
                        ? '<span style="color:red;">BELUM LUNAS</span>'
                        : '<span style="color:green;">LUNAS</span>' !!}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" align="center">Tidak ada tagihan</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot style="background-color: #ededed; font-weight: bold;">
        <tr>
            <td colspan="3">Total Tagihan</td>
            <td align="right">@rupiah($totalTagihan ?? 0)</td>
            <td rowspan="3"></td>
        </tr>
        <tr>
            <td colspan="3">Total Tagihan Terbayar</td>
            <td align="right">@rupiah($totalTerbayar ?? 0)</td>
        </tr>
        <tr>
            <td colspan="3">Total Sisa Tagihan</td>
            <td align="right">@rupiah($sisaTagihan ?? 0)</td>
        </tr>
        </tfoot>
    </table>
@endsection