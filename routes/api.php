<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\BankCategoryController;
use App\Http\Controllers\InsuranceSimulationController;
use App\Models\Organization;
use App\Models\Member;
use App\Models\Exam;
use App\Models\PdfUpload;



Route::get('/dashboard/stats', function () {
    return response()->json([
        'examCount' => Exam::where('created_at', '>=', now()->startOfMonth())->count(),
        'pdfCount'  => PdfUpload::where('status', 'pending')
            ->where('created_at', '>=', now()->startOfMonth())->count(),
    ]);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/calculate-premium', [InsuranceSimulationController::class, 'calculate']);

Route::get('/banks', [BankController::class, 'index']);
Route::get('/branches', [BankController::class, 'branches']);
Route::get('/bank-categories', [BankCategoryController::class, 'index']);

Route::get('/zipcode/{zip}', function ($zip) {
    $zip = preg_replace('/[^0-9]/', '', $zip);

    if (strlen($zip) !== 7) {
        return response()->json(['results' => []]);
    }

    $response = Http::get(
        'https://zipcloud.ibsnet.co.jp/api/search',
        ['zipcode' => $zip]
    );

    return $response->json();
});

// Autocomplete用
Route::get('/organizations/search', function (Request $request) {

    $keyword = $request->q;

    return Organization::query()
        ->where('name', 'like', "%{$keyword}%")
        ->orderBy('name')
        ->limit(20)
        ->get(['id', 'name', 'abbr'])
        ->map(fn ($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'label' => $o->name . ($o->abbr ? ' (' . $o->abbr . ')' : ''),
        ]);
});

Route::get('/organizations/{id}', function ($id) {
    $o = \App\Models\Organization::findOrFail($id);

    return [
        'id' => $o->id,
        'name' => $o->name,
        'label' => $o->name . ($o->abbr ? ' (' . $o->abbr . ')' : ''),
    ];
});
// Autocomplete用
Route::get('/members/search', function (Request $request) {

    $keyword = $request->q;

    return Member::query()
        ->where('name', 'like', "%{$keyword}%")
        ->orderBy('name')
        ->limit(20)
        ->get(['id', 'name'])
        ->map(fn ($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'label' => $o->name,
        ]);
});

Route::get('/members/{id}', function ($id) {
    $o = \App\Models\Member::findOrFail($id);

    return [
        'id' => $o->id,
        'name' => $o->name,
        'label' => $o->name,
    ];
});
