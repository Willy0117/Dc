<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetLocaleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Profile\MemberController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\MemberController as MemberRegController;
use App\Http\Controllers\BankSearchController;
use App\Http\Controllers\PreRegister\PreRegisterController;
use App\Http\Controllers\PreRegister\EmailVerifyController;

Route::prefix('admin')->name('admin.')->group(function () {

    /**
     * -------------------------------
     * ① 管理者ログイン（guest のみ）
     * -------------------------------
     */
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });


    /**
     * -------------------------------
     * ② 認証後（auth）＋ロール（admin/super_admin）
     * -------------------------------
     */
    Route::middleware(['auth', 'role:admin|super_admin'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // ダッシュボード
        Route::get('/dashboard', fn() => inertia('Admin/Dashboard'))->name('dashboard');
    });
});
/*
Route::get('/', function () {
    return redirect()->route('login');
});
*/
Route::middleware(['auth', 'verified'])->group(function () {
    //Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::post('users/bulk-delete', [\App\Http\Controllers\UserController::class, 'bulkDelete'])->name('users.bulkDelete');
    // Tenant
    Route::resource('tenants', \App\Http\Controllers\TenantController::class);
    Route::post('tenants/bulk-delete', [\App\Http\Controllers\TenantController::class, 'bulkDelete'])->name('tenants.bulkDelete');
    // Role
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::post('roles/bulk-delete', [\App\Http\Controllers\RoleController::class, 'bulkDelete'])->name('roles.bulkDelete');
    // Members 
    Route::get('/profile/member', [MemberController::class, 'edit'])->name('profile.member.edit');
    Route::put('/profile/member', [MemberController::class, 'update'])->name('profile.member.update');
    // Organizations 
    Route::get('/profile/organization', [OrganizationController::class, 'edit'])->name('profile.organization.edit');
    Route::put('/profile/organization', [OrganizationController::class, 'update'])->name('profile.organization.update');
        // 他の認証が必要なルートもここに追加
});
// メール仮登録
Route::prefix('pre-register')->name('pre-register.')->group(function () {
    // メール入力画面
    Route::get('/mail', function () { return inertia('PreRegister/Email'); })->name('mail');
    // 仮登録 → メール送信
    Route::post('/pre', [PreRegisterController::class, 'store'])->name('pre');
    // メール確認
    Route::get('/verify/{token}', [EmailVerifyController::class, 'verify'])->name('verify');
    // メール完了
    Route::get('/thanks', function () { return inertia('PreRegister/Thanks'); })->name('thanks'); 
});


Route::prefix('members')->group(function () {

    Route::get('pdf', [MemberRegController::class, 'pdf']);

    Route::get('register/{token}', 
        [MemberRegController::class, 'showRegistrationForm']
    )->name('members.register');
    Route::post('members/agree/{token}', [MemberRegController::class, 'agreeNext'])
    ->name('members.register.agree');
    Route::get('register/{token}/register', 
        [MemberRegController::class, 'showRegisterForm']
    )->name('members.register.register');
    Route::post('register/{token}', 
        [MemberRegController::class, 'completeRegistration']
    )->name('members.register.complete');
    // 完了画面GET
    Route::get('members/register/complete', function () {
        return Inertia::render('Members/Complete', [
            'success' => session('success'),
            'member_id' => session('member_id'), // 必要なら
        ]);
    })->name('members.complete');



    // 加盟団体加入で拒否された場合のメッセージ画面
    Route::get('register/{token}/rejected', [MemberRegController::class, 'showRejectedMessage'])
        ->name('members.register.rejected');
/*
    Route::get('pdfcreate', [MemberRegController::class, 'showPdfForm'])
    ->name('members.pdfcreate');
*/
    Route::get('bank', [MemberRegController::class, 'bank'])
        ->name('members.bank');

    Route::get('pdfcreate', [MemberRegController::class, 'pdfCreate'])
        ->name('members.pdfcreate');

    Route::post('pdfgenerate', [MemberRegController::class, 'pdfGenerate'])
        ->name('members.pdfgenerate');    
    Route::get('pdf-preview/{token}', 
        [MemberRegController::class, 'pdfPreview']
    )->name('members.pdf.preview');

});

Route::get('/banks/search', [BankSearchController::class, 'banks'])
  ->name('banks.search');

Route::get('/branches/search', [BankSearchController::class, 'branches'])
  ->name('branches.search');

  Route::get('/test', function () {
    return Inertia::render('Test');
});


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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
