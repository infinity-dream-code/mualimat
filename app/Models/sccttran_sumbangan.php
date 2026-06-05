<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sccttran_sumbangan extends Model
{
    protected $connection = 'DATA_MYSQL';

    protected $table = 'sccttran_sumbangan';

    protected $primaryKey = 'urut';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'idSumbangan',
        'METODE',
        'TRXDATE',
        'DEBET',
        'KREDIT',
    ];
}
