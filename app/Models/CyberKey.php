<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class CyberKey extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'mysql';

    protected $table = "cyber_key";

    protected $primaryKey = "urut";

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        "users",
        "kunci",
        "fid",
        "ket",
        "kel",
        "urut",
        "password",
    ];

    protected $hidden = ["password"];

    public function getAuthIdentifierName(): string
    {
        return "urut";
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password;
    }

    public function getIdAttribute(): int
    {
        return (int) $this->urut;
    }

    public function getNameAttribute(): string
    {
        return $this->ket ?: (string) $this->users;
    }

    public function getUsernameAttribute(): string
    {
        return (string) $this->users;
    }

    /**
     * Kode sekolah dari cyber_key.fid (nyambung mst_sekolah.CODE01).
     * Contoh: admmts=23 (MTS), admma=63 (MA). Kosong = akses semua.
     */
    public function getUnitAttribute(): ?string
    {
        $fid = trim((string) ($this->attributes['fid'] ?? ''));

        return $fid !== '' ? $fid : null;
    }

    /** Normalisasi kel: cashless | kantin | admfak | other */
    public function getKelGroupAttribute(): string
    {
        $kel = strtolower(trim((string) ($this->attributes['kel'] ?? '')));
        $kel = str_replace([' ', '-', '_'], '', $kel);

        if (in_array($kel, ['cashless', 'cashles', 'cashlesss'], true)) {
            return 'cashless';
        }

        if ($kel === 'kantin') {
            return 'kantin';
        }

        if (in_array($kel, ['admfak', 'adminfak', 'adm'], true) || $kel === '') {
            return 'admfak';
        }

        return 'admfak';
    }

    public function isCashless(): bool
    {
        return $this->kel_group === 'cashless';
    }

    public function isKantin(): bool
    {
        return $this->kel_group === 'kantin';
    }

    public function isAdmfak(): bool
    {
        return $this->kel_group === 'admfak';
    }

    /**
     * Apakah user boleh melihat grup menu sidebar.
     * - cashless: beranda + smartcard saja
     * - kantin: beranda + smartcard (sub: transaksi belanja & tap belanja)
     * - admfak: semua menu (data tetap difilter fid jika ada)
     */
    public function canSeeMenu(string $menu): bool
    {
        $menu = strtolower(trim($menu));
        $group = $this->kel_group;

        if (in_array($menu, ['beranda', 'logout'], true)) {
            return true;
        }

        if ($group === 'cashless') {
            return $menu === 'smartcard';
        }

        if ($group === 'kantin') {
            return in_array($menu, ['smartcard', 'transaksi-belanja', 'tap-belanja'], true);
        }

        return true;
    }

    public function canSeeSmartcardItem(string $item): bool
    {
        $item = strtolower(trim($item));

        // Tap Belanja hanya untuk kel kantin (bukan admfak / cashless)
        if ($item === 'tap-belanja') {
            return $this->isKantin();
        }

        if ($this->isKantin()) {
            return in_array($item, ['transaksi-belanja', 'tap-belanja'], true);
        }

        // admfak & cashless: semua item smartCARD kecuali tap-belanja
        return true;
    }

    /** Path admin yang diizinkan (prefix tanpa leading slash), null = semua. */
    public function allowedAdminPrefixes(): ?array
    {
        if ($this->isCashless()) {
            return ['admin', 'admin/smartcard'];
        }

        if ($this->isKantin()) {
            return [
                'admin',
                'admin/smartcard/transaksi-belanja',
                'admin/smartcard/tap-belanja',
            ];
        }

        return null;
    }

    public function defaultHomePath(): string
    {
        if ($this->isKantin()) {
            return '/admin/smartcard/transaksi-belanja';
        }

        if ($this->isCashless()) {
            return '/admin/smartcard/data-kartu-siswa';
        }

        return '/admin';
    }

    public function hasRole(string $role): bool
    {
        if ($role === "siswa") {
            return false;
        }

        if (in_array($role, ["admin", "super-admin"], true)) {
            return true;
        }

        return strcasecmp((string) $this->kel, $role) === 0;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
    }
}
