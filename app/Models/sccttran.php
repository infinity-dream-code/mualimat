<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sccttran extends Model
{
    protected $connection = "DATA_MYSQL";

    protected $table = 'sccttran';

    protected $primaryKey = 'AA';

    public $timestamps = false;

    public $incrementing = false;
}
