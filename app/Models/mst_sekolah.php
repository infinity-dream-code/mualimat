<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mst_sekolah extends Model
{
    protected $table = 'mst_sekolah';

    protected $primaryKey = 'urut';

    public $timestamps = false;

    public $incrementing = false;
}
