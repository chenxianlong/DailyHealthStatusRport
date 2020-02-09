<?php

namespace App\WeChatWork;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = "wechat_work_account";

    protected $guarded = [];

    public function applications()
    {
        return $this->hasMany(Application::class, "account_id");
    }

    /*
    public function users()
    {
        return $this->hasMany(User::class, "account_id");
    }
    */
}
