<?php


namespace App\WeChatWork;


use App\WeChatWork\Contract\AccessTokenRepository;
use App\WeChatWork\Contract\ApplicationRepository;
use Illuminate\Support\Facades\DB;

class AccessTokenDBRepository implements AccessTokenRepository
{
    public function getAccessTokenByApplicationId($applicationId): AccessToken
    {
        $application = Application::query()->findOrFail($applicationId);
        return new AccessToken($application->access_token, $application->access_token_expire_at);
    }

    public function lockAccessTokenForUpdateByApplicationId($applicationId): AccessToken
    {
        DB::beginTransaction();
        $application = Application::query()->where("id", $applicationId)->lockForUpdate()->first();
        return new AccessToken($application->access_token, $application->access_token_expire_at);
    }

    public function updateAccessTokenByApplicationId($applicationId, AccessToken $accessToken)
    {
        Application::query()->where("id", $applicationId)->update([
            "access_token" => $accessToken->accessToken,
            "access_token_expire_at" => $accessToken->expireAt,
        ]);
    }

    public function commitAccessToken()
    {
        DB::commit();
    }

    public function rollbackAccessToken()
    {
        DB::rollBack();
    }
}
