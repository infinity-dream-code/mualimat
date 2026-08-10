<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sholat_user extends Model
{
    protected $connection = "DATA_MYSQL";

    protected $table = "sholat_user";

    protected $primaryKey = "idincrement";

    public $timestamps = false;

    protected $fillable = [
        "username",
        "password",
        "nama",
        "role",
    ];

    protected $hidden = [
        "password",
    ];
}
