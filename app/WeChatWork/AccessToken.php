<?php
declare(strict_types=1);

namespace App\WeChatWork;


class AccessToken
{
    public $accessToken;

    public $expireAt;

    public function __construct($accessToken = null, $expireAt = 0)
    {
        $this->accessToken = $accessToken;
        $this->expireAt = $expireAt;
    }
}
