<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mst_kelas extends Model
{
    protected $connection = "DATA_MYSQL";

    protected $table = "mst_kelas";

    protected $primaryKey = "id";

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        "kelas",
        "jenjang",
        "unit",
        "kelompok",
    ];

    public static function getMstKelasAttributes(): array|object
    {
        return static::select(["id", "kelas", "jenjang", "unit"])
            ->orderByRaw("
                    CASE
                        WHEN unit LIKE '%SD%' THEN 1
                        WHEN unit LIKE '%SMP%' THEN 2
                        WHEN unit LIKE '%SMA%' THEN 3
                        ELSE 4
                    END
            ")
            ->orderByRaw("CASE WHEN jenjang REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, jenjang")
            ->orderBy("kelas")
            ->get();
    }
}
