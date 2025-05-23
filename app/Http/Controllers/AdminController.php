<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\master_data\mst_kelas;
use App\Models\master_data\mst_post;
use App\Models\master_data\mst_siswa;
use App\Models\master_data\mst_thn_aka;
use App\Models\scctbill;
use App\Models\scctcust;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $months = [
            'January' => 'Jan',
            'February' => 'Feb',
            'March' => 'Mar',
            'April' => 'Apr',
            'May' => 'Mei',
            'June' => 'Jun',
            'July' => 'Jul',
            'August' => 'Ags',
            'September' => 'Sep',
            'October' => 'Okt',
            'November' => 'Nov',
            'December' => 'Des'
        ];

        $today = Carbon::now();

        $taighanDibayar = Scctbill::select(
            [
                DB::raw('DATE(PAIDDT) as date'),
                DB::raw('COUNT(*) as count')
            ])
            ->where('PAIDDT', '>=', Carbon::now()->subDays(7))
            ->where('FSTSBolehBayar', 1)
            ->groupBy(DB::raw('DATE(PAIDDT)'))
            ->orderBy('date', 'ASC')
            ->get();

        $hasilTagihanDibayar = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->subDays($i)->format('Y-m-d');
            $hasilTagihanDibayar[$date] = 0;
        }
        foreach ($taighanDibayar as $count) {
            $hasilTagihanDibayar[$count->date] = $count->count;
        }
        $chartTagihanDibayar = collect($hasilTagihanDibayar)->map(function ($count, $date) use ($months) {
            return [
                'date' => $this->formatDateIndonesian($date, $months),
                'count' => $count
            ];
        })->values();

        $data['chartTagihanDibayar'] = $chartTagihanDibayar;
        $data['tagihan_baru_dibayar'] = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
            ->select([
                'scctbill.AA',
                'scctbill.BILLNM',
                'scctbill.BILLAM',
                'scctbill.PAIDST',
                'scctbill.PAIDDT',
                'scctbill.BTA',
                'scctbill.FIDBANK',
                'scctbill.FUrutan',
                'scctcust.nmcust as nama',
                'scctcust.nocust',
                'scctcust.CODE02',
                'scctcust.DESC02',
                'scctcust.DESC04',
            ])->where('scctbill.PAIDST', 1)
            ->where('scctbill.FSTSBolehBayar', 1)
            ->where('scctcust.STCUST', 1)
            ->orderBy('PAIDDT', 'desc')->take(5)->get();

        $data['jumlah_tagihan_belum_dibayar'] = scctbill::where('PAIDST', 0)->count('AA') ?: 0;
        $data['jumlah_tagihan_dibayar'] = scctbill::where('PAIDST', 1)->count('AA') ?: 0;

        return view('admin.index', $data);
    }

    function formatDateIndonesian($date, $months)
    {
        // Create a Carbon instance from the date
        $carbonDate = Carbon::parse($date);

        // Get the day and month in English
        $day = $carbonDate->day;
        $monthName = $carbonDate->format('F');

        // Translate month to Indonesian
        $monthIndonesian = $months[$monthName] ?? $monthName;

        // Return formatted date
        return $monthIndonesian . ' ' . $day;
    }
}
