<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScctbillCut extends Model
{
    protected $table = 'scctbill_cut';
    protected $primaryKey = 'ID';
    protected $fillable = [
        'ID',
        'AA',
        'BILLNM',
        'BTA',
        'BILLCD',
        'BILLAM',
        'BILL_CUT',
        'REASON',
        'CREATED_AT',
        'USER_ID'
    ];
}
