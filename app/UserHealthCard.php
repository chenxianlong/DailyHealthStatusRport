<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserHealthCard extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = "user_id";

    protected $guarded = [];
}
