<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KARTU TAGIHAN SISWA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
        }
        .info-siswa {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .info-siswa table {
            width: 100%;
        }
        .info-siswa td {
            padding: 3px 5px;
            font-size: 12px;
        }
        .info-siswa .label {
            font-weight: bold;
            width: 120px;
        }
        table.tagihan {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.tagihan th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            font-size: 11px;
        }
        table.tagihan td {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: center;
            font-size: 11px;
        }
        table.tagihan td.text-left {
            text-align: left;
        }
        table.tagihan td.text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 15px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        .total-section table {
            width: 100%;
            max-width: 400px;
            margin-left: auto;
        }
        .total-section td {
            padding: 3px 8px;
            font-size: 12px;
        }
        .total-section .label {
            font-weight: bold;
        }
        .total-section .amount {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
        }
        .footer .line {
            margin-top: 30px;
            border-top: 1px solid #000;
            width: 200px;
            margin-left: auto;
            padding-top: 5px;
        }
        .status-lunas {
            color: green;
            font-weight: bold;
        }
        .status-belum {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Mu'allimaat Muhammadiyah Yogyakarta</h1>
        <p>Jl. Suronatan Blok NG II No.653, Notoprajan, Ngampilan, Kota Yogyakarta</p>
        <p>No. Telp: 0823 2883 2011, E-mail: pengaduan.muallimaat@gmail.com</p>
        <h2 style="margin-top:15px;">KARTU TAGIHAN SISWA</h2>
    </div>

    <div class="info-siswa">
        <table>
            <tr>
                <td class="label">NIS</td>
                <td>: {{ $siswa->nocust ?? '-' }}</td>
                <td class="label">Kelas</td>
                <td>: {{ $siswa->DESC02 ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">NOVA</td>
                <td>: {{ $nova ?? '-' }}</td>
                <td class="label">Unit</td>
                <td>: {{ $siswa->CODE02 ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Siswa</td>
                <td>: {{ $siswa->nmcust ?? '-' }}</td>
                <td class="label">Angkatan</td>
                <td>: {{ $siswa->DESC04 ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="tagihan">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Tahun Akademik</th>
                <th>Nama Tagihan</th>
                <th style="width:120px;">Jumlah</th>
                <th style="width:120px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tagihans as $index => $tagihan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $tagihan->BTA ?? '-' }}</td>
                <td class="text-left">
                    {{ $tagihan->BILLNM ?? '-' }}
                    @if(isset($tagihan->FUrutan))
                        <span style="font-size:9px;color:#999;"> (Urutan: {{ $tagihan->FUrutan }})</span>
                    @endif
                </td>
                <td class="text-right">Rp. {{ number_format($tagihan->BILLAM ?? 0, 0, ',', '.') }}</td>
                <td>
                    @if(($tagihan->PAIDST ?? 0) == 1)
                        <span class="status-lunas">LUNAS</span>
                    @else
                        <span class="status-belum">BELUM LUNAS</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;">Tidak ada tagihan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-section">
        <table>
            <tr>
                <td class="label">Total Tagihan</td>
                <td class="amount">Rp. {{ number_format($totalTagihan ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Tagihan Terbayar</td>
                <td class="amount">Rp. {{ number_format($totalTerbayar ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label" style="font-size:14px;">Total Sisa Tagihan</td>
                <td class="amount" style="font-size:14px; font-weight:bold; color:red;">Rp. {{ number_format($sisaTagihan ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <div>
            YOGYAKARTA, {{ strtoupper(\Carbon\Carbon::now()->translatedFormat('l, d F Y')) }}
        </div>
        <div class="line">
            ADMIN
        </div>
    </div>
</body>
</html>