<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mst_thn_aka extends Model
{
    protected $connection = "DATA_MYSQL";

    protected $table = 'mst_thn_aka';

    protected $primaryKey = 'urut';

    public $timestamps = false;

    public $incrementing = false;
}
