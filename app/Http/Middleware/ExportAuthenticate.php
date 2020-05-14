<?php

namespace App\Http\Middleware;

use App\ExportUserIdWhiteList;
use App\UserAllowExportDepartment;
use App\UserAllowExportSelfDepartment;
use App\WeChatWork\SessionUtils;
use Closure;

class ExportAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * @var SessionUtils $sessionUtils
         */
        $sessionUtils = resolve(SessionUtils::class);
        if (is_null($sessionUtils->getUser()) || (is_null(ExportUserIdWhiteList::query()->find($sessionUtils->getUser()->id)) && UserAllowExportDepartment::query()->where("user_id", $sessionUtils->getUser()->id)->count() === 0) && is_null(UserAllowExportSelfDepartment::query()->find($sessionUtils->getUser()->id))) {
            abort(403);
        }
        return $next($request);
    }
}
