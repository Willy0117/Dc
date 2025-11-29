<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Masters\OperatorController;
use App\Http\Controllers\Api\Masters\DeviceController;
use App\Http\Controllers\Api\Masters\SensorController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\TemperatureLogController;
use App\Http\Controllers\Api\ProcessController;
use App\Http\Controllers\RehabApplicationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 認証なし
Route::prefix('v1')->group(function () {

    Route::prefix('masters')->group(function () {
        Route::get('/operators', [OperatorController::class, 'index']);
        Route::get('/devices', [DeviceController::class, 'index']);
        Route::get('/sensors', [SensorController::class, 'index']);
    });

    Route::get('/tenants', [TenantController::class, 'index']);

    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/processes', [ProcessController::class, 'index']);

    Route::get('/temperature-logs', [TemperatureLogController::class, 'index']);  // 一覧取得
    Route::post('/temperature-logs', [TemperatureLogController::class, 'store']); // 登録

});
