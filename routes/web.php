<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetLocaleController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::post('users/bulk-delete', [\App\Http\Controllers\UserController::class, 'bulkDelete'])->name('users.bulkDelete');
    // Tenant
    Route::resource('tenants', \App\Http\Controllers\TenantController::class);
    Route::post('tenants/bulk-delete', [\App\Http\Controllers\TenantController::class, 'bulkDelete'])->name('tenants.bulkDelete');
    // Role
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::post('roles/bulk-delete', [\App\Http\Controllers\RoleController::class, 'bulkDelete'])->name('roles.bulkDelete');

    // 権限割当フォーム（GET）
    Route::get('permissions/{permission}/assign', [\App\Http\Controllers\PermissionController::class, 'assign'])
        ->name('permissions.assign');

    // 権限割当実行（POST）
    Route::post('permissions/{permission}/assign', [\App\Http\Controllers\PermissionController::class, 'assignStore'])
        ->name('permissions.assign.store');

    // Permission
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
    Route::post('permissions/bulk-delete', [\App\Http\Controllers\PermissionController::class, 'bulkDelete'])->name('permissions.bulkDelete');

    Route::resource('temperatures', \App\Http\Controllers\TemperatureController::class);

    // autocomplete用（Ajax）
    Route::get('/menus/autocomplete', [\App\Http\Controllers\MenuController::class, 'autocomplete']);
    Route::get('/sensors/autocomplete', [\App\Http\Controllers\SensorController::class, 'autocomplete']);
    Route::get('/devices/autocomplete', [\App\Http\Controllers\DeviceController::class, 'autocomplete']);
    Route::get('/operators/autocomplete', [\App\Http\Controllers\OperatorController::class, 'autocomplete']);

    // Sensor 
    Route::resource('sensors', \App\Http\Controllers\SensorController::class);
    Route::post('sensors/bulk-delete', [\App\Http\Controllers\SensorController::class, 'bulkDelete'])->name('sensors.bulkDelete');
    // Device 
    Route::resource('devices', \App\Http\Controllers\DeviceController::class);
    Route::post('devices/bulk-delete', [\App\Http\Controllers\DeviceController::class, 'bulkDelete'])->name('devices.bulkDelete');
    // Operators 
    Route::resource('operators', \App\Http\Controllers\OperatorController::class);
    Route::post('operator/bulk-delete', [\App\Http\Controllers\OperatorController::class, 'bulkDelete'])->name('operators.bulkDelete');
    // Processes 
    Route::resource('processes', \App\Http\Controllers\ProcessController::class);
    Route::post('process/bulk-delete', [\App\Http\Controllers\ProcessController::class, 'bulkDelete'])->name('processes.bulkDelete');

    // Menus
    // Import画面表示
    Route::get('menus/import', [\App\Http\Controllers\MenuController::class, 'showImportForm'])
        ->name('menus.import');

    // Import処理
    Route::post('menus/import', [\App\Http\Controllers\MenuController::class, 'importExcel'])
        ->name('menus.import.store');

    Route::get('menus/weekly', [\App\Http\Controllers\MenuController::class, 'weekly'])->name('menus.weekly');

    Route::resource('menus', \App\Http\Controllers\MenuController::class);
    Route::post('menus/bulk-delete', [\App\Http\Controllers\MenuController::class, 'bulkDelete'])->name('menus.bulkDelete');

    // Sensor コード重複チェック用（Ajax）
    Route::post('sensors/checkCode', [\App\Http\Controllers\SensorController::class, 'checkCode'])
        ->name('sensors.checkCode');    
    // Device コード重複チェック用（Ajax）
    Route::post('devices/checkCode', [\App\Http\Controllers\DeviceController::class, 'checkCode'])
        ->name('devices.checkCode');

    // Sensor SerialNumber コード重複チェック用（Ajax）
    Route::post('sensors/checkSerialNumber', [\App\Http\Controllers\SensorController::class, 'checkSerialNumber'])
        ->name('sensors.checkSerialNumber'); 

        // 他の認証が必要なルートもここに追加
});
/*
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
*/
Route::post('/locale', function (Request $request) {
    $locale = $request->input('locale', 'en');
    session(['locale' => $locale]);
    app()->setLocale($locale);
    return response()->json(['status' => 'ok']);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
