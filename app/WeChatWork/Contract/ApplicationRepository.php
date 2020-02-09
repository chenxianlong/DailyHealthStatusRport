<?php


namespace App\WeChatWork\Contract;


interface ApplicationRepository
{
    public function findByIdOrFail($applicationId): object;
}
