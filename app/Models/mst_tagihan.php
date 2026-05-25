<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mst_tagihan extends Model
{
    protected $connection = "DATA_MYSQL";

    protected $table = "mst_tagihan";

    protected $primaryKey = "urut";

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        "tagihan",
        "kode",
    ];
}
