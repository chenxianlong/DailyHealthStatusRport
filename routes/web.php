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

Route::middleware(App\Http\Middleware\WechatWork\AuthenticatedRequired::class . ":1")->group(function () {
    Route::get('/', "SPAController");
    Route::get("/status", "HealthReportController@status");
    Route::get("/healthReport", "HealthReportController@showForm");
    Route::post("/healthReport", "HealthReportController@store");
    Route::get("/bind", "SPAController")->name("users.bind");
    Route::post("/bind", "UserController@bindIDCardNo");
    Route::get("/export", "SPAController");
    Route::get("/export/all", "ExportController@exportAll")->name("export.all");
    Route::get("/export/notReported", "ExportController@exportNotReported")->name("export.notReported");
    Route::get("/export/status", "ExportController@status")->name("export.status");
    Route::post("/export/authenticate", "ExportController@authenticate")->name("export.authenticate");
});

