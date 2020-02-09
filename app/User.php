<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    public $timestamps = false;

    protected $guarded = [];

    public function healthCard()
    {
        return $this->hasOne(UserHealthCard::class, "user_id", "id");
    }

    public function dailyHealthStatus()
    {
        return $this->hasMany(UserDailyHealthStatus::class, "user_id", "id");
    }
}
