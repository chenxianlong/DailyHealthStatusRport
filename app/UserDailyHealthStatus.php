<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDailyHealthStatus extends Model
{
    use CompositeKeyModel;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = ["reported_date", "user_id"];

    protected $guarded = [];
}
