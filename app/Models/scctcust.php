<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class scctcust extends Model
{
    protected $table = "scctcust";

    protected $primaryKey = "CUSTID";

    public $timestamps = false;

    public $incrementing = false;

    public static function showVAMTS($nis): string
    {
        $prefix = "751023";
        $nova = str_pad($nis, 10, "0", STR_PAD_LEFT);
        return "$prefix$nova";
    }

    public static function showVAMA($nis): string
    {
        $prefix = "797763";
        $nova = str_pad($nis, 10, "0", STR_PAD_LEFT);
        return "$prefix$nova";
    }
}
