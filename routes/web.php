<?php
use \Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(App\Http\Middleware\WechatWork\AuthenticatedRequired::class . ":" . env("HR_APPLICATION_ID"))->group(function () {
    Route::get('/', "SPAController");
    Route::get("/bind", "SPAController")->name("users.bind");
    Route::post("/bind", "UserController@bindIDCardNo");
    Route::get("/healthStatus/daily", "UserDailyHealthStatusController@showForm");
    Route::post("/healthStatus/daily", "UserDailyHealthStatusController@store");
    Route::post("/healthCard", "UserHealthCardController@store");
    Route::get("/status", "UserDailyHealthStatusController@status");
    Route::get("/export", "SPAController");
    Route::post("/export/all", "ExportController@exportAll")->name("export.all");
    Route::post("/export/notReported", "ExportController@exportNotReported")->name("export.notReported");
    Route::get("/export/status", "ExportController@status")->name("export.status");
    Route::post("/export/authenticate", "ExportController@authenticate")->name("export.authenticate");
});

Route::get("/export/download", "ExportController@download")->name("export.download");

