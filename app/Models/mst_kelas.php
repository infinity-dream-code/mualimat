<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mst_kelas extends Model
{
    protected $table = "mst_kelas";

    protected $connection = "DATA_MYSQL";

    protected $primaryKey = "urut";

    public $timestamps = false;

    public $incrementing = false;
}
