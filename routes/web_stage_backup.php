<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetLocaleController;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\RehabApplicationController;
use App\Http\Controllers\Profile\MemberController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Profile\OrganizationController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\PdfUploadController;
use App\Http\Controllers\Admin\PdfUploadController as AdminPdfUploadController;
use App\Http\Controllers\Admin\CreditCategoryController;
use App\Http\Controllers\Admin\CreditConferenceController;
use App\Http\Controllers\Admin\CreditRoleController;
use App\Http\Controllers\Admin\CreditController;
use App\Http\Controllers\Admin\InstructorMemberController;
use App\Http\Controllers\Admin\InstructorUpdateCycleController;

Route::middleware(['auth', 'verified'])->group(function () {
});


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin'])->get('/dashboard', fn() => inertia('Admin/Dashboard'))->name('dashboard');

    Route::get('/pdf-uploads', [AdminPdfUploadController::class, 'index'])->name('pdf_uploads.index');
    Route::post('/pdf-uploads/{pdf}/approve', [AdminPdfUploadController::class, 'approve'])->name('pdf_uploads.approve');
    Route::post('/pdf-uploads/{pdf}/reject', [AdminPdfUploadController::class, 'reject'])->name('pdf_uploads.reject');
    Route::get('/pdf-uploads/{pdf}/view', [AdminPdfUploadController::class, 'view'])->name('pdf_uploads.view');
    Route::get('/pdf-uploads/{pdf}/thumbnail', [AdminPdfUploadController::class, 'thumbnail'])->name('pdf_uploads.thumbnail');
    // 指導士会員一覧
    Route::get('instructorMembers', [InstructorMemberController::class, 'index'])
        ->name('instructorMembers.index');

    // 指導士会員詳細（PDF一覧）
    Route::get('instructorMembers/{member}', [InstructorMemberController::class, 'show'])
        ->name('instructorMembers.show');
    // インストラクター更新サイクルの審査結果送信
    Route::post('instructorUpdateCycles/{cycle}/review',[InstructorUpdateCycleController::class, 'review']
        )->name('instructorUpdateCycles.review');
    // PDF承認 / Reject
    Route::post('pdf/{upload}/approve', [PdfUploadController::class, 'approve'])
        ->name('pdf.approve');

    Route::post('pdf/{upload}/reject', [PdfUploadController::class, 'reject'])
            ->name('pdf.reject');
    // 管理画面で一覧表示
    Route::get('/rehab-applications', [RehabApplicationController::class, 'index'])
        ->name('admin.rehab.index');
    Route::post('/rehab-applications/{application}/reject', [RehabApplicationController::class, 'reject'])
        ->name('rehab.reject');
    Route::post('/rehab-applications/{application}/approve', [RehabApplicationController::class, 'approve'])
        ->name('rehab.approve');
    Route::resource('credit-categories', CreditCategoryController::class);
    Route::resource('credit-conferences', CreditConferenceController::class);
    Route::resource('credit-roles', CreditRoleController::class);
    Route::resource('credits', CreditController::class);       
});

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

    Route::get('/pdf-uploads', [PdfUploadController::class, 'index'])->name('pdf_uploads.index');
    Route::post('/pdf-uploads', [PdfUploadController::class, 'store'])->name('pdf_uploads.store');
    Route::post('/pdf-uploads/{pdfUpload}', [PdfUploadController::class, 'update'])->name('pdf_uploads.update');
   // PDFを会員が閲覧
    Route::get('/pdf-uploads/{pdf}/view', [PdfUploadController::class, 'view'])->name('pdf_uploads.view');
    // サムネイルを返す
    Route::get('/pdf-uploads/{pdf}/thumbnail', [PdfUploadController::class, 'thumbnail'])->name('pdf_uploads.thumbnail');

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
    // ----------------------------------------
    // ユーザー向け
    // ----------------------------------------

    // 自己申告フォーム
    Route::get('/rehab-apply', [RehabApplicationController::class, 'create'])
        ->name('rehab.create');

    // 自己申告フォーム保存
    Route::post('/rehab-apply', [RehabApplicationController::class, 'store'])
        ->name('rehab.store');

    // PDFアップロード画面
    Route::get('/rehab-apply/files', [RehabApplicationController::class, 'editFiles'])
        ->name('rehab.files.edit');

    // PDF個別アップロード
    Route::post('/rehab-apply/files', [RehabApplicationController::class, 'uploadPdf'])
        ->name('rehab.files.upload');

    // autocomplete用（Ajax）
    Route::get('/menus/autocomplete', [\App\Http\Controllers\MenuController::class, 'autocomplete']);
    Route::get('/sensors/autocomplete', [\App\Http\Controllers\SensorController::class, 'autocomplete']);
    Route::get('/devices/autocomplete', [\App\Http\Controllers\DeviceController::class, 'autocomplete']);
    Route::get('/operators/autocomplete', [\App\Http\Controllers\OperatorController::class, 'autocomplete']);
    Route::get('/profile/organizations/autocomplete', [\App\Http\Controllers\Profile\OrganizationController::class, 'autocomplete']);

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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
