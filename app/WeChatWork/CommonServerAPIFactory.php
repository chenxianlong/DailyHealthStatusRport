<?php
declare(strict_types=1);

namespace App\WeChatWork;

use App\WeChatWork\Contract\AccessTokenRefreshHookFactory;
use App\WeChatWork\Contract\AccessTokenRepository;
use App\WeChatWork\Contract\ApplicationRepository;
use App\WeChatWork\Contract\ServerAPI;
use App\WeChatWork\Contract\ServerAPIFactory;

class CommonServerAPIFactory implements ServerAPIFactory
{
    private $applicationRepository;

    private $accessTokenRepository;

    private $accessTokenRefreshHookFactory;


    public function __construct(ApplicationRepository $applicationRepository, AccessTokenRepository $accessTokenRepository)
    {
        $this->applicationRepository = $applicationRepository;
        $this->accessTokenRepository = $accessTokenRepository;

        $this->accessTokenRefreshHookFactory = new StoreNewAccessTokenFactory($this->accessTokenRepository);
    }

    /**
     * @param AccessTokenRefreshHookFactory $accessTokenRefreshHookFactory
     * @return CommonServerAPIFactory
     */
    public function setAccessTokenRefreshHookFactory(AccessTokenRefreshHookFactory $accessTokenRefreshHookFactory): CommonServerAPIFactory
    {
        $this->accessTokenRefreshHookFactory = $accessTokenRefreshHookFactory;
        return $this;
    }

    public function make($applicationId): ServerAPI
    {
        $application = $this->applicationRepository->findByIdOrFail($applicationId);
        $accessToken = $this->accessTokenRepository->getAccessTokenByApplicationId($applicationId);
        /**
         * @var ServerAPI $serverAPI
         */
        $serverAPI = resolve(ServerAPI::class);
        $serverAPI
            ->setAppId($application->account->app_id)
            ->setAgentId($application->agent_id)
            ->setSecret($application->secret)
            ->setAccessToken($accessToken)
            ->setAccessTokenRefreshHook($this->accessTokenRefreshHookFactory->make($applicationId))
        ;
        return $serverAPI;
    }
}
