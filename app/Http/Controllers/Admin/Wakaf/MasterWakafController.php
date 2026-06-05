<?php

namespace App\Http\Controllers\Admin\Wakaf;

use App\Http\Controllers\Controller;
use App\Models\mst_sumbangan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterWakafController extends Controller
{
    public string $title = 'Wakaf';
    public string $mainTitle = 'Wakaf';
    public string $dataTitle = 'Master Wakaf';

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['columnsUrl'] = route('admin.wakaf.master-wakaf.get-column');
        $data['datasUrl'] = route('admin.wakaf.master-wakaf.get-data');

        return view('admin.wakaf.master_wakaf.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => null, 'name' => 'no', 'columnType' => 'row', 'className' => 'text-center'],
            ['data' => 'idincrement', 'name' => 'ID', 'searchable' => true, 'orderable' => true],
            ['data' => 'nmsumbangan', 'name' => 'Nama Sumbangan', 'searchable' => true, 'orderable' => true],
            ['data' => 'nocust', 'name' => 'No VA', 'searchable' => true, 'orderable' => true],
            ['data' => 'stcust_label', 'name' => 'Status', 'searchable' => false, 'orderable' => true, 'className' => 'text-center'],
            [
                'data' => 'toggle',
                'name' => '',
                'dataVal' => true,
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'action',
                'buttonText' => 'Aktif / Nonaktif',
                'buttonClass' => 'btn btn-sm btn-warning btn-toggle-status',
                'buttonIcon' => 'ri-repeat-line me-1',
                'noCaption' => false,
            ],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = (int) $request->get('start', 0);
        $rowperpage = (int) $request->get('length', 10);

        $columnIndexArr = $request->get('order', []);
        $columnNameArr = $request->get('columns', []);
        $searchArr = $request->get('search', []);
        $searchValue = $searchArr['value'] ?? '';

        $filterNama = trim((string) $request->input('filter.nama', ''));
        $filterStatus = (string) $request->input('filter.status', 'all');

        $defaultColumn = 'idincrement';
        $defaultOrder = 'desc';

        if (!empty($columnIndexArr)) {
            $columnIndex = $columnIndexArr[0]['column'] ?? 0;
            $columnName = $columnNameArr[$columnIndex]['data'] ?? $defaultColumn;
            $columnSortOrder = $columnIndexArr[0]['dir'] ?? $defaultOrder;
        } else {
            $columnName = $defaultColumn;
            $columnSortOrder = $defaultOrder;
        }

        $allowedSort = ['idincrement', 'nmsumbangan', 'nocust', 'stcust_label'];
        if (!in_array($columnName, $allowedSort, true)) {
            $columnName = $defaultColumn;
        }

        $baseQuery = mst_sumbangan::query()
            ->select(['idincrement', 'nmsumbangan', 'nocust', 'stcust']);

        $applyFilters = function ($query) use ($searchValue, $filterNama, $filterStatus) {
            if ($searchValue !== '') {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('nmsumbangan', 'like', '%' . $searchValue . '%')
                        ->orWhere('nocust', 'like', '%' . $searchValue . '%');
                });
            }

            if ($filterNama !== '') {
                $query->where('nmsumbangan', 'like', '%' . $filterNama . '%');
            }

            if (in_array($filterStatus, ['0', '1'], true)) {
                $query->where('stcust', (int) $filterStatus);
            }
        };

        $totalRecords = (clone $baseQuery)->count();
        $filteredQuery = (clone $baseQuery);
        $applyFilters($filteredQuery);
        $totalRecordswithFilter = (clone $filteredQuery)->count();

        if ($columnName === 'stcust_label') {
            $filteredQuery->orderBy('stcust', $columnSortOrder);
        } else {
            $filteredQuery->orderBy($columnName, $columnSortOrder);
        }

        $records = $filteredQuery
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(function ($item) {
                $status = (int) ($item->stcust ?? 0);
                $item->stcust_label = $status === 1
                    ? '<span class="badge bg-label-success">Aktif</span>'
                    : '<span class="badge bg-label-danger">Nonaktif</span>';
                $item->toggle = true;
                $item->item_id = $item->idincrement;
                return $item;
            })
            ->toArray();

        return response()->json([
            'draw' => (int) $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecordswithFilter,
            'data' => $records,
        ]);
    }

    public function toggleStatus($id)
    {
        $data = mst_sumbangan::query()->where('idincrement', $id)->first();
        if (!$data) {
            return response()->json(['message' => 'Data wakaf tidak ditemukan'], 422);
        }

        try {
            DB::connection('DATA_MYSQL')->beginTransaction();
            $newStatus = (int) $data->stcust === 1 ? 0 : 1;
            $data->update(['stcust' => $newStatus]);
            DB::connection('DATA_MYSQL')->commit();

            return response()->json([
                'message' => $newStatus === 1 ? 'Status wakaf diubah menjadi Aktif' : 'Status wakaf diubah menjadi Nonaktif',
            ]);
        } catch (Exception $e) {
            DB::connection('DATA_MYSQL')->rollBack();
            return response()->json(['message' => 'Gagal mengubah status wakaf', 'error' => $e->getMessage()], 422);
        }
    }
}
