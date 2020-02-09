<?php

namespace App\Providers;

use App\Network\Contract\RequestFactory;
use App\Network\CURLRequestFactory;
use App\WeChatWork\AccessTokenDBRepository;
use App\WeChatWork\ApplicationDBRepository;
use App\WeChatWork\CommonServerAPIFactory;
use App\WeChatWork\Contract\AccessTokenRepository;
use App\WeChatWork\Contract\ApplicationRepository;
use App\WeChatWork\Contract\ServerAPI;
use App\WeChatWork\Contract\ServerAPIFactory;
use App\WeChatWork\HTTPServerAPI;
use App\WeChatWork\SessionUtils;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RequestFactory::class, CURLRequestFactory::class);

        $this->app->bind(ServerAPI::class, HTTPServerAPI::class);
        $this->app->bind(ApplicationRepository::class, ApplicationDBRepository::class);
        $this->app->bind(AccessTokenRepository::class, AccessTokenDBRepository::class);
        $this->app->bind(ServerAPIFactory::class, CommonServerAPIFactory::class);

        $this->app->singleton(SessionUtils::class, function ($app) {
            return new SessionUtils(request()->session(), env("HR_APPLICATION_ID"));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
