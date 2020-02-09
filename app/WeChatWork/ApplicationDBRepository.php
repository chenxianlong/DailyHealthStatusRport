<?php


namespace App\WeChatWork;


use App\WeChatWork\Contract\ApplicationRepository;

class ApplicationDBRepository implements ApplicationRepository
{
    public function findByIdOrFail($applicationId): object
    {
        return Application::query()->with("account")->findOrFail($applicationId);
    }
}
