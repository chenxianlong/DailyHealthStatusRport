<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExportUserIdWhiteList extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = "user_id";

    protected $guarded = [];
}
