<?php

namespace App\Http\Middleware;

use App\ExportUserIdWhiteList;
use App\UserAllowExportDepartment;
use App\WeChatWork\SessionUtils;
use Closure;

class ExportAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * @var SessionUtils $sessionUtils
         */
        $sessionUtils = resolve(SessionUtils::class);
        if (is_null($sessionUtils->getUser()) || (is_null(ExportUserIdWhiteList::query()->find($sessionUtils->getUser()->id) && is_null(UserAllowExportDepartment::query()->where("user_id", $sessionUtils->getUser()->id))) && strlen($sessionUtils->getUserId()) !== 8)) {
            abort(403);
        }
        return $next($request);
    }
}
