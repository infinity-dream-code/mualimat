<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\sholat_user;
use App\Models\ValidationMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateMusrifahController extends Controller
{
    public string $title = 'Update Musrifah';
    public string $mainTitle = 'Update Musrifah';
    public string $dataTitle = 'Update Musrifah';

    private const ROLE_MUSRIFAH = 'Musrifah';

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['columnsUrl'] = route('admin.update-musrifah.get-column');
        $data['datasUrl'] = route('admin.update-musrifah.get-data');

        return view('admin.update_musrifah.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => null, 'name' => 'no', 'className' => 'text-center', 'columnType' => 'row'],
            ['data' => 'username', 'name' => 'Username', 'searchable' => true, 'orderable' => true],
            ['data' => 'nama', 'name' => 'Nama', 'searchable' => true, 'orderable' => true],
            ['data' => 'role', 'name' => 'Role', 'searchable' => true, 'orderable' => true],
            [
                'data' => 'edit',
                'name' => '',
                'dataVal' => false,
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'modal',
                'buttonText' => 'Edit',
                'buttonClass' => 'btn btn-sm btn-info btn-edit',
                'buttonLink' => '#modal-edit',
                'buttonIcon' => 'ri-edit-line me-2',
            ],
            [
                'data' => 'delete',
                'name' => '',
                'dataVal' => false,
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'modal',
                'buttonText' => 'Hapus',
                'buttonClass' => 'btn btn-sm btn-danger btn-hapus',
                'buttonLink' => '#modal-delete',
                'buttonIcon' => 'ri-delete-bin-line me-2',
            ],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start');
        $rowperpage = $request->get('length');

        $columnIndex_arr = $request->get('order', []);
        $columnName_arr = $request->get('columns', []);
        $order_arr = $request->get('order', []);
        $search_arr = $request->get('search', []);
        $searchValue = $search_arr['value'] ?? '';

        $columnName = 'nama';
        $columnSortOrder = 'asc';

        if (!empty($order_arr)) {
            $columnIndex = $columnIndex_arr[0]['column'] ?? null;
            if ($columnIndex !== null && !empty($columnName_arr[$columnIndex]['data']) && $columnName_arr[$columnIndex]['data'] !== 'no') {
                $columnName = $columnName_arr[$columnIndex]['data'];
                $columnSortOrder = $order_arr[0]['dir'] ?? 'asc';
            }
        }

        $searchable = ['username', 'nama', 'role'];
        $allowedSort = ['username', 'nama', 'role'];
        if (!in_array($columnName, $allowedSort, true)) {
            $columnName = 'nama';
        }

        $baseQuery = sholat_user::query()->where('role', self::ROLE_MUSRIFAH);

        $totalRecords = (clone $baseQuery)->count();
        $filteredQuery = (clone $baseQuery)
            ->when($searchValue !== '', function ($query) use ($searchable, $searchValue) {
                $query->where(function ($q) use ($searchable, $searchValue) {
                    foreach ($searchable as $column) {
                        $q->orWhere($column, 'like', '%' . $searchValue . '%');
                    }
                });
            });

        $totalRecordswithFilter = (clone $filteredQuery)->count();

        $records = $filteredQuery
            ->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->get(['idincrement', 'username', 'nama', 'role'])
            ->map(function ($item) {
                $item->item_id = $item->idincrement;
                $item->edit = true;
                $item->delete = true;
                unset($item->idincrement);
                return $item;
            })->toArray();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecordswithFilter,
            'data' => $records,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'username' => [
                        'required',
                        'string',
                        'max:100',
                        Rule::unique('sholat_user', 'username')->connection('DATA_MYSQL'),
                    ],
                    'nama' => ['required', 'string', 'max:255'],
                    'password' => ['required', 'string', 'min:1', 'max:128'],
                ],
                ValidationMessage::messages(),
                array_merge(ValidationMessage::attributes(), [
                    'username' => 'Username',
                    'password' => 'Password',
                ])
            );

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }

            DB::connection('DATA_MYSQL')->beginTransaction();

            sholat_user::create([
                'username' => trim($request->username),
                'nama' => trim($request->nama),
                'password' => $this->hashPassword($request->password),
                'role' => self::ROLE_MUSRIFAH,
            ]);

            DB::connection('DATA_MYSQL')->commit();

            return response()->json(['message' => 'Data ' . $this->mainTitle . ' telah disimpan']);
        } catch (Exception $e) {
            $this->safeRollback();
            return response()->json(['message' => 'Data ' . $this->mainTitle . ' gagal disimpan', 'error' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = sholat_user::where('idincrement', $id)
                ->where('role', self::ROLE_MUSRIFAH)
                ->first();

            if (!$user) {
                return response()->json(['message' => 'Data Musrifah tidak ditemukan!'], 422);
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'username' => [
                        'required',
                        'string',
                        'max:100',
                        Rule::unique('sholat_user', 'username')
                            ->connection('DATA_MYSQL')
                            ->ignore($user->idincrement, 'idincrement'),
                    ],
                    'nama' => ['required', 'string', 'max:255'],
                    'password' => ['nullable', 'string', 'min:1', 'max:128'],
                ],
                ValidationMessage::messages(),
                array_merge(ValidationMessage::attributes(), [
                    'username' => 'Username',
                    'password' => 'Password',
                ])
            );

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }

            DB::connection('DATA_MYSQL')->beginTransaction();

            $payload = [
                'username' => trim($request->username),
                'nama' => trim($request->nama),
            ];

            if ($request->filled('password')) {
                $payload['password'] = $this->hashPassword($request->password);
            }

            $user->update($payload);

            DB::connection('DATA_MYSQL')->commit();

            return response()->json(['message' => 'Data ' . $this->mainTitle . ' telah diubah']);
        } catch (Exception $e) {
            $this->safeRollback();
            return response()->json(['message' => 'Data ' . $this->mainTitle . ' gagal diubah', 'error' => $e->getMessage()], 422);
        }
    }

    private function hashPassword(string $password): string
    {
        // Format password di tabel sholat_user memakai MD5 (32 karakter),
        // contoh: md5('123') = 202cb962ac59075b964b07152d234b70
        return md5($password);
    }

    private function safeRollback(): void
    {
        try {
            $connection = DB::connection('DATA_MYSQL');
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
        } catch (Exception $e) {
            // abaikan error rollback agar tidak menimpa pesan error utama
        }
    }

    public function destroy($id)
    {
        $user = sholat_user::where('idincrement', $id)
            ->where('role', self::ROLE_MUSRIFAH)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Data Musrifah tidak ditemukan!'], 422);
        }

        try {
            DB::connection('DATA_MYSQL')->beginTransaction();
            $user->delete();
            DB::connection('DATA_MYSQL')->commit();

            return response()->json(['message' => 'Data ' . $this->mainTitle . ' telah dihapus']);
        } catch (Exception $e) {
            $this->safeRollback();
            return response()->json(['message' => 'Data ' . $this->mainTitle . ' gagal dihapus', 'error' => $e->getMessage()], 422);
        }
    }
}
