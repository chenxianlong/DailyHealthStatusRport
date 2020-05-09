<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student2FindIdCardNo extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = "no";
}
