<?php

namespace App\WeChatWork;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table = "wechat_work_applications";

    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class, "account_id");
    }
}
