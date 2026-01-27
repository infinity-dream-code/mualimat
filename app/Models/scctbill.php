<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class scctbill extends Model
{
    protected $connection = "DATA_MYSQL";

    protected $table = 'scctbill';

    protected $primaryKey = 'AA';

    public $timestamps = false;

    public $incrementing = false;
}
