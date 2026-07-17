<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Throwable;

class ApprovalPrestasiController extends Controller
{
    private const SESSION_KEY = 'approval_prestasi_user';

    public function loginForm(): View|RedirectResponse
    {
        if ($this->authUser() !== null) {
            return redirect()->route('approval-prestasi.index');
        }

        return view('approval_prestasi.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:128'],
        ]);

        try {
            $user = DB::connection('DATA_MYSQL')
                ->table('user_prestasi')
                ->where('username', $validated['username'])
                ->first();
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Tidak dapat terhubung ke database user prestasi. Hubungi admin.',
            ]);
        }

        if (!$user) {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Username atau password salah.',
            ]);
        }

        if (!$this->verifyPassword($validated['password'], (string) ($user->password ?? ''))) {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Username atau password salah.',
            ]);
        }

        $role = strtolower(trim((string) ($user->role ?? '')));
        if ($role === '' || $role === 'siswa') {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Akun siswa tidak bisa mengakses approval.',
            ]);
        }

        Session::put(self::SESSION_KEY, [
            'id' => (string) ($user->idincrement ?? ''),
            'username' => trim((string) ($user->username ?? '')),
            'nama' => trim((string) ($user->nama ?? '')),
            'role' => trim((string) ($user->role ?? '')),
            'code01' => trim((string) ($user->code01 ?? '')),
        ]);

        return redirect()->route('approval-prestasi.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Session::forget(self::SESSION_KEY);
        $request->session()->regenerateToken();

        return redirect()->route('approval-prestasi.login-form');
    }

    public function index(): View|RedirectResponse
    {
        $auth = $this->authUser();
        if ($auth === null) {
            return redirect()->route('approval-prestasi.login-form');
        }

        return view('approval_prestasi.index', [
            'authUser' => $auth,
        ]);
    }

    public function getData(Request $request): JsonResponse
    {
        $auth = $this->authUser();
        if ($auth === null) {
            return response()->json(['message' => 'Sesi habis, silakan login ulang'], 401);
        }

        $status = (string) $request->query('isapproved', 'all');

        $query = DB::connection('DATA_MYSQL')
            ->table('aka_reward as ar')
            ->leftJoin('scctcust as sc', 'sc.CUSTID', '=', 'ar.custid')
            ->leftJoin('mst_sekolah as ms', 'ms.CODE01', '=', 'sc.CODE01')
            ->select([
                'ar.id',
                'ar.custid',
                'ar.nocust',
                'ar.nmcust',
                'ar.kelas',
                'ar.jenis_prestasi',
                'ar.keterangan',
                'ar.nilai_penghargaan',
                'ar.bta',
                'ar.url',
                'ar.isapproved',
                'ar.approveddate',
                'ar.approvedby',
                'ar.created_at',
                'sc.CODE01 as code01',
                DB::raw('COALESCE(ms.DESC01, "-") as sekolah'),
            ]);

        if ($auth['code01'] !== '') {
            $query->where('sc.CODE01', $auth['code01']);
        }

        if (in_array($status, ['0', '1'], true)) {
            $query->where('ar.isapproved', (int) $status);
        }

        $rows = $query
            ->orderByDesc('ar.created_at')
            ->orderByDesc('ar.id')
            ->limit(1000)
            ->get();

        return response()->json([
            'items' => $rows,
            'total' => $rows->count(),
            'scope_code01' => $auth['code01'],
        ]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $auth = $this->authUser();
        if ($auth === null) {
            return response()->json(['message' => 'Sesi habis, silakan login ulang'], 401);
        }

        if (!$this->canAccessReward($id, $auth['code01'])) {
            return response()->json(['message' => 'Data tidak ditemukan atau di luar akses sekolah'], 404);
        }

        DB::connection('DATA_MYSQL')
            ->table('aka_reward')
            ->where('id', $id)
            ->update([
                'isapproved' => 1,
                'approveddate' => now(),
                'approvedby' => $auth['nama'] !== '' ? $auth['nama'] : $auth['username'],
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Data berhasil di-approve']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $auth = $this->authUser();
        if ($auth === null) {
            return response()->json(['message' => 'Sesi habis, silakan login ulang'], 401);
        }

        if (!$this->canAccessReward($id, $auth['code01'])) {
            return response()->json(['message' => 'Data tidak ditemukan atau di luar akses sekolah'], 404);
        }

        DB::connection('DATA_MYSQL')
            ->table('aka_reward')
            ->where('id', $id)
            ->update([
                'isapproved' => 0,
                'approveddate' => null,
                'approvedby' => null,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Data berhasil ditolak']);
    }

    private function authUser(): ?array
    {
        $user = Session::get(self::SESSION_KEY);
        if (!is_array($user)) {
            return null;
        }

        return [
            'id' => (string) ($user['id'] ?? ''),
            'username' => (string) ($user['username'] ?? ''),
            'nama' => (string) ($user['nama'] ?? ''),
            'role' => (string) ($user['role'] ?? ''),
            'code01' => (string) ($user['code01'] ?? ''),
        ];
    }

    private function canAccessReward(int $id, string $code01): bool
    {
        $query = DB::connection('DATA_MYSQL')
            ->table('aka_reward as ar')
            ->leftJoin('scctcust as sc', 'sc.CUSTID', '=', 'ar.custid')
            ->where('ar.id', $id);

        if ($code01 !== '') {
            $query->where('sc.CODE01', $code01);
        }

        return $query->exists();
    }

    private function verifyPassword(string $password, string $stored): bool
    {
        if ($stored === '') {
            return false;
        }

        $info = password_get_info($stored);
        if ($info['algo'] !== null) {
            return password_verify($password, $stored);
        }

        $lower = strtolower($stored);
        if (hash_equals($lower, sha1($password))) {
            return true;
        }
        if (hash_equals($lower, hash('sha256', $password))) {
            return true;
        }
        if (hash_equals($lower, md5($password))) {
            return true;
        }

        return hash_equals($stored, $password);
    }
}

