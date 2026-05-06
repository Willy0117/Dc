<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetLocaleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\InstructorMemberController as AdminInstructorMemberController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\AnnualFeeController as AdminAnnualFeeController;
use App\Http\Controllers\Admin\PdfUploadController as AdminPdfUploadController;
use App\Http\Controllers\Admin\MemberImportController;

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PdfUploadController;
use App\Http\Controllers\RehabApplicationController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AnnualFeeController;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


Route::get('/compose-image', [\App\Http\Controllers\PrintController::class, 'composeImage'])->name('composeImage');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {

        Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');

        Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.post');
    });

    // 認証後
    Route::middleware(['auth:admin'])->group(function () {

        Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])
            ->name('logout');

        Route::get('/dashboard', fn () => inertia('Admin/Dashboard'))
            ->name('dashboard');
        // Tenant
        Route::resource('tenants', \App\Http\Controllers\Admin\TenantController::class);
        Route::post('tenants/bulk-delete', [\App\Http\Controllers\Admin\TenantController::class, 'bulkDelete'])->name('tenants.bulkDelete');
        // Role
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::post('roles/bulk-delete', [\App\Http\Controllers\Admin\RoleController::class, 'bulkDelete'])->name('roles.bulkDelete');
        // Permission
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
        Route::post('permissions/bulk-delete', [\App\Http\Controllers\Admin\PermissionController::class, 'bulkDelete'])->name('permissions.bulkDelete');
        Route::post('permissions/assign', [\App\Http\Controllers\Admin\PermissionController::class, 'assign'])->name('permissions.assign');
        // user
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        // admin
        Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class);

        // organization
        Route::resource('members', \App\Http\Controllers\Admin\MemberController::class);
        // organization
        Route::resource('organizations', \App\Http\Controllers\Admin\OrganizationController::class);

        Route::resource('annual-fees', \App\Http\Controllers\Admin\AnnualFeeController::class);

        // exams　指導士試験申込
        Route::put(
            'exams/{exam}/status',
            [\App\Http\Controllers\Admin\ExamController::class, 'updateStatus']
        )->name('exams.updateStatus');

        Route::resource('exams', \App\Http\Controllers\Admin\ExamController::class);

        Route::post(
            'applications/{application}/upload-document',
            [\App\Http\Controllers\Admin\ApplicationController::class, 'uploadDocument']
        )->name('applications.uploadDocument');

        Route::get(
            'applications/{application}/print-document',
            [\App\Http\Controllers\Admin\ApplicationController::class, 'printDocument']
        )->name('applications.printDocument');

        Route::put(
            'applications/{application}/status',
            [\App\Http\Controllers\Admin\ApplicationController::class, 'updateStatus']
        )->name('applications.updateStatus');

        Route::get(
            'applications/fax',
            [\App\Http\Controllers\Admin\ApplicationController::class, 'fax']
        )->name('applications.fax');

        Route::post(
            'applications/faxstore',
            [\App\Http\Controllers\Admin\ApplicationController::class, 'faxstore']
        )->name('applications.faxstore');

        Route::resource(
            'applications',
            \App\Http\Controllers\Admin\ApplicationController::class
        )->only(['index','show','create','store']);

        Route::get('pdf-uploads', [AdminPdfUploadController::class, 'index'])->name('pdf-uploads.index');
        Route::post('pdf-uploads/{pdf}/approve', [AdminPdfUploadController::class, 'approve'])->name('pdf-uploads.approve');
        Route::post('pdf-uploads/{pdf}/reject', [AdminPdfUploadController::class, 'reject'])->name('pdf-uploads.reject');
        Route::get('pdf-uploads/{pdf}/view', [AdminPdfUploadController::class, 'view'])->name('pdf-uploads.view');
        Route::get('pdf-uploads/{pdf}/thumbnail', [AdminPdfUploadController::class, 'thumbnail'])->name('pdf-uploads.thumbnail');
        // 指導士会員一覧
        Route::get('instructorMembers', [AdminInstructorMemberController::class, 'index'])
            ->name('instructorMembers.index');

        // 指導士会員詳細（PDF一覧）
        Route::get('instructorMembers/{member}', [AdminInstructorMemberController::class, 'show'])
            ->name('instructorMembers.show');
        // インストラクター更新サイクルの審査結果送信
        Route::post('instructorUpdateCycles/{cycle}/review',[AdminInstructorUpdateCycleController::class, 'review']
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

        Route::prefix('member')->name('member.')->group(function () {
            Route::get('/import', [MemberImportController::class, 'index'])->name('import');

            Route::post('/import', [MemberImportController::class, 'store'])->name('import.store');

            Route::get('/', [AdminMemberController::class, 'index'])->name('index');
            Route::get('/pdf/{id}', [AdminMemberController::class, 'pdfPreview'])->name('pdf.preview');
            Route::get('/{member}', [AdminMemberController::class, 'show'])->name('show');
            Route::get('/{member}/edit', [AdminMemberController::class, 'edit'])->name('edit');
            Route::put('/{member}', [AdminMemberController::class, 'update'])->name('update');
            // routes/admin.php
            Route::get('{member}/status/edit', [AdminMemberController::class, 'editStatus'])
                ->name('editStatus');

            Route::put('{member}/status', [AdminMemberController::class, 'updateStatus'])
                ->name('updateStatus');

            Route::get('/{member}/progress/edit', [AdminMemberController::class, 'editProgress'])
                ->name('editProgress');
            Route::put('/{member}/progress', [AdminMemberController::class, 'updateProgress'])
                ->name('updateProgress');
            Route::post('/{member}/upload-document', [AdminMemberController::class, 'uploadDocument'])
                ->name('uploadDocument');
              
        });
    });
});

// 未ログインユーザー用
//Route::middleware('guest:web')->group(function () {
//    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
//    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
//});



Route::get('/test-mail', function () {
    $pdfPath = 'poem/pdf/P00000002.pdf';
    $filePath = Storage::path($pdfPath);

    // 確認（最初だけ）
    if (!file_exists($filePath)) {
        dd('ファイルが存在しない', $filePath);
    }

    \Mail::raw('PDF添付テストです', function ($message) use ($filePath) {
        $message->to('dev@coo-net.co.jp')
                ->subject('PDF添付テスト')
                ->attach($filePath);
    });

    return 'sent';
});

Route::get('/pdf', [\App\Http\Controllers\PDFController::class, 'index']);


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

Route::get('/insurance-simulation', function () {
    return Inertia::render('InsuranceSimulation');
});

Route::post('/locale', function (Request $request) {
    $locale = $request->input('locale', 'en');
    session(['locale' => $locale]);
    app()->setLocale($locale);
    return response()->json(['status' => 'ok']);
});

Route::middleware([
    'auth:web',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('applications/{application}/upload-document', [ApplicationController::class, 'uploadDocument'])->name('applications.uploadDocument');
    Route::get('applications/{application}/print-document', [ApplicationController::class, 'printDocument'])->name('applications.printDocument');
    Route::put('applications/{application}/status', [ApplicationController::class, 'updateStatus'])->name('updateStatus');


    //Route::post('applications/pdf-generate', [ApplicationController::class, 'pdfGenerate'])->name('applications.pdfGenerate');
    Route::get('applications/pdf-generate', [ApplicationController::class, 'pdfGenerate'])->name('applications.pdfGenerate');

    Route::get('applications/fax', [ApplicationController::class, 'fax'])->name('applications.fax');
    Route::post('applications/faxstore', [ApplicationController::class, 'faxstore'])->name('applications.faxstore');

   // PDFを会員が閲覧
    Route::get('/pdf-uploads/{pdf}/view', [PdfUploadController::class, 'view'])->name('pdf-uploads.view');
    // サムネイルを返す
    Route::get('/pdf-uploads/{pdf}/thumbnail', [PdfUploadController::class, 'thumbnail'])->name('pdf-uploads.thumbnail');

    Route::resource('applications', ApplicationController::class);

    Route::resource('pdf-uploads', PdfUploadController::class);
    
    Route::resource('exams', ExamController::class);
    Route::resource('reports', ReportController::class);

    Route::resource('annual-fees', AnnualFeeController::class);
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



});
/*
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
*/
