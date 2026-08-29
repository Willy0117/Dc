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
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\WebhookLogController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\StripeController;
use App\Http\Controllers\Admin\LicenseFeeController;
use App\Http\Controllers\Admin\StorageController;

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\StripeWebhookController;
// 症例報告
use App\Http\Controllers\Admin\CaseReportController as AdminCaseReportController;
use App\Http\Controllers\CaseReportController;
use App\Http\Controllers\Admin\FormFieldController as AdminFormFieldController;

use App\Http\Controllers\ProcedureVideoController;
use App\Http\Controllers\Admin\ProcedureVideoController as AdminProcedureVideoController;
use App\Http\Controllers\ReferenceVideoController;
use App\Http\Controllers\Admin\ReferenceVideoController as AdminReferenceVideoController;
use App\Http\Controllers\Admin\ReferenceVideoCategoryController as AdminReferenceVideoCategoryController;

use App\Http\Controllers\ResourceDocumentController;
use App\Http\Controllers\Admin\ResourceDocumentController as AdminResourceDocumentController;
use App\Http\Controllers\Admin\ResourceDocumentCategoryController as AdminResourceDocumentCategoryController;
use App\Http\Controllers\LicenseController;

use App\Http\Controllers\Admin\ElearningAttemptController as AdminElearningAttemptController;
use App\Http\Controllers\Admin\ElearningQuestionController as AdminElearningQuestionController;
use App\Http\Controllers\ElearningController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ProfileChangeLogController as AdminProfileChangeLogController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\ElearningInvitationController;

use App\Http\Controllers\Admin\MemberNameMatchController as AdminMemberNameMatchController;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::get('/compose-image', [\App\Http\Controllers\PrintController::class, 'composeImage'])->name('composeImage');

Route::prefix('applications')->name('applications.')->group(function () {
    Route::get('/register',  [\App\Http\Controllers\ApplicationController::class, 'register'])->name('register');
    Route::post('/register', [\App\Http\Controllers\ApplicationController::class, 'registerStore'])->name('register.store');
    Route::get('/confirm',   [\App\Http\Controllers\ApplicationController::class, 'confirm'])->name('confirm');
    Route::get('/contract',  [\App\Http\Controllers\ApplicationController::class, 'contract'])->name('contract');
    Route::post('/sign',     [\App\Http\Controllers\ApplicationController::class, 'sign'])->name('sign');
    Route::get('/complete',  [\App\Http\Controllers\ApplicationController::class, 'complete'])->name('complete');
    Route::get('/stripe-complete', [\App\Http\Controllers\ApplicationController::class, 'stripeComplete'])->name('stripe_complete');
});

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
        
        Route::resource('notices', \App\Http\Controllers\Admin\NoticeController::class);
        Route::get('notices-api/organizations', [\App\Http\Controllers\Admin\NoticeController::class, 'searchOrganizations'])->name('notices.search-organizations');
        Route::get('notices-api/members', [\App\Http\Controllers\Admin\NoticeController::class, 'searchMembers'])->name('notices.search-members');
    
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
        // Members
        Route::get('members/organizations/search', [\App\Http\Controllers\Admin\MemberController::class, 'searchOrganizations'])->name('members.organizations.search');
        Route::post('members/bulk-delete', [\App\Http\Controllers\Admin\MemberController::class, 'bulkDelete'])->name('members.bulkDelete');
        Route::patch('members/{member}/status', [\App\Http\Controllers\Admin\MemberController::class, 'updateStatus'])->name('members.updateStatus');
        Route::resource('members', \App\Http\Controllers\Admin\MemberController::class);

        // Organizations
        Route::get('organizations/create', [\App\Http\Controllers\Admin\OrganizationController::class, 'edit'])->name('organizations.create');
        Route::resource('organizations', \App\Http\Controllers\Admin\OrganizationController::class)->except(['create']);
        Route::post('organizations/bulk-delete', [\App\Http\Controllers\Admin\OrganizationController::class, 'bulkDelete'])->name('organizations.bulkDelete');
        Route::post('organizations/{organization}/send-invitation', [\App\Http\Controllers\Admin\OrganizationController::class, 'sendInvitation'])->name('organizations.send-invitation');
        Route::post('organizations/{organization}/upgrade-tier',   [\App\Http\Controllers\Admin\OrganizationController::class, 'upgradeTier'])->name('organizations.upgrade-tier');
 
        Route::post('organizations/{organization}/downgrade-tier', [\App\Http\Controllers\Admin\OrganizationController::class, 'downgradeTier'])->name('organizations.downgrade-tier');
        
        Route::post('organizations/bulk-send-invitation', [\App\Http\Controllers\Admin\OrganizationController::class, 'bulkSendInvitation'])->name('organizations.bulk-send-invitation');
        // ━━━ リマインダーメール一括送信 ━━━
        Route::post('organizations/bulk-send-reminder', [\App\Http\Controllers\Admin\OrganizationController::class, 'bulkSendReminder'])
            ->name('organizations.bulk-send-reminder');

        // ━━━ 自由記述メール一括送信 ━━━
        Route::post('organizations/bulk-send-mail', [\App\Http\Controllers\Admin\OrganizationController::class, 'bulkSendMail'])
            ->name('organizations.bulk-send-mail');
            
        Route::get('organizations/{organization}/fee', [\App\Http\Controllers\Admin\OrganizationController::class, 'fee'])->name('organizations.fee');
        Route::post('organizations/{id}/license', [\App\Http\Controllers\Admin\OrganizationController::class, 'issueLicense'])->name('organizations.license');
        Route::post('organizations/{id}/license/mail', [\App\Http\Controllers\Admin\OrganizationController::class, 'mailLicense'])->name('organizations.license.mail');
        Route::post('organizations/invoice', [\App\Http\Controllers\Admin\OrganizationController::class, 'createInvoice'])->name('organizations.invoice');

        Route::post('organizations/stripe-payment', [\App\Http\Controllers\Admin\OrganizationController::class, 'createStripePayment'])->name('organizations.stripe_payment');
        //        Route::resource('organizations', \App\Http\Controllers\Admin\OrganizationController::class);
        Route::get('webhook-logs/unread-count', [WebhookLogController::class, 'unreadCount'])->name('webhook_logs.unread_count');
        Route::get('webhook-logs', [WebhookLogController::class, 'index'])->name('webhook_logs.index');
        Route::post('webhook-logs/mark-all-read', [WebhookLogController::class, 'markAllRead'])->name('webhook_logs.mark_all_read');
        // ──────────────────────────────────────────────────────────────
        // ライセンス料金マスター
        // ──────────────────────────────────────────────────────────────
        Route::get('license-fees', [LicenseFeeController::class, 'index'])->name('license-fees.index');
        Route::post('license-fees', [LicenseFeeController::class, 'store'])->name('license-fees.store');
        // ──────────────────────────────────────────────────────────────
        // 請求書
        // ──────────────────────────────────────────────────────────────
        Route::resource('invoices', InvoiceController::class)
            ->only(['index', 'show', 'update', 'destroy']);
        
        Route::post('invoices/{invoice}/resend-email',
            [InvoiceController::class, 'resendEmail']
        )->name('invoices.resendEmail');
        
        // ──────────────────────────────────────────────────────────────
        // Stripe 管理
        // ──────────────────────────────────────────────────────────────
        Route::get('stripe',
            [StripeController::class, 'index']
        )->name('stripe.index');
        
        Route::post('stripe/payment-link',[StripeController::class, 'paymentLink'])->name('stripe.payment-link');

        Route::post('stripe/{invoice}/resend-email', [\App\Http\Controllers\Admin\StripeController::class, 'resendEmail'])->name('stripe.resendEmail');

        Route::get('storage', [\App\Http\Controllers\Admin\StorageController::class, 'index'])->name('storage.index');
        // Admin側 auth:admin グループ内に追加
        Route::get('form-fields', [AdminFormFieldController::class, 'index'])->name('form-fields.index');
        Route::post('form-fields', [AdminFormFieldController::class, 'store'])->name('form-fields.store');
        Route::post('form-fields/{formField}/toggle', [AdminFormFieldController::class, 'toggle'])->name('form-fields.toggle');
        Route::delete('form-fields/{formField}', [AdminFormFieldController::class, 'destroy'])->name('form-fields.destroy');
        Route::post('form-fields/{formField}/store-option', [AdminFormFieldController::class, 'storeOption'])->name('form-fields.store-option');
        Route::post('form-fields/options/{formOption}/toggle', [AdminFormFieldController::class, 'toggleOption'])->name('form-fields.toggle-option');
        Route::delete('form-fields/options/{formOption}', [AdminFormFieldController::class, 'destroyOption'])->name('form-fields.destroy-option');
        // 症例報告
        Route::get('case-reports', [App\Http\Controllers\Admin\CaseReportController::class, 'index'])->name('case-reports.index');
        Route::get('case-reports/{caseReport}', [App\Http\Controllers\Admin\CaseReportController::class, 'show'])->name('case-reports.show');
        Route::delete('case-reports/{caseReport}', [App\Http\Controllers\Admin\CaseReportController::class, 'destroy'])->name('case-reports.destroy');
        // ──────────────────────────────────────────
        // 手技動画（管理画面）
        // ──────────────────────────────────────────
        Route::get('procedure-videos', [App\Http\Controllers\Admin\ProcedureVideoController::class, 'index'])->name('procedure-videos.index');
        Route::delete('procedure-videos/{procedureVideo}', [App\Http\Controllers\Admin\ProcedureVideoController::class, 'destroy'])->name('procedure-videos.destroy');
        // ──────────────────────────────────────────
        // 参考動画（管理画面側）※admin.プレフィックスの既存グループに追加
        // ──────────────────────────────────────────
        Route::get('/reference-videos', [AdminReferenceVideoController::class, 'index'])->name('reference-videos.index');
        Route::post('/reference-videos', [AdminReferenceVideoController::class, 'store'])->name('reference-videos.store');
        Route::put('/reference-videos/{referenceVideo}', [AdminReferenceVideoController::class, 'update'])->name('reference-videos.update');
        Route::delete('/reference-videos/{referenceVideo}', [AdminReferenceVideoController::class, 'destroy'])->name('reference-videos.destroy');
        Route::post('/reference-videos/reorder', [AdminReferenceVideoController::class, 'reorder'])->name('reference-videos.reorder');
        Route::get('/reference-videos-views', [AdminReferenceVideoController::class, 'views'])->name('reference-videos.views');
        Route::post('/reference-video-categories', [AdminReferenceVideoCategoryController::class, 'store'])->name('reference-video-categories.store');
        Route::put('/reference-video-categories/{referenceVideoCategory}', [AdminReferenceVideoCategoryController::class, 'update'])->name('reference-video-categories.update');
        Route::delete('/reference-video-categories/{referenceVideoCategory}', [AdminReferenceVideoCategoryController::class, 'destroy'])->name('reference-video-categories.destroy');
        Route::post('/reference-video-categories/reorder', [AdminReferenceVideoCategoryController::class, 'reorder'])->name('reference-video-categories.reorder');
 
        // ──────────────────────────────────────────
        // 資料（管理画面側）※admin.プレフィックスの既存グループに追加
        // ──────────────────────────────────────────
        Route::get('/resource-documents', [AdminResourceDocumentController::class, 'index'])->name('resource-documents.index');
        Route::post('/resource-documents', [AdminResourceDocumentController::class, 'store'])->name('resource-documents.store');
        Route::put('/resource-documents/{resourceDocument}', [AdminResourceDocumentController::class, 'update'])->name('resource-documents.update');
        Route::delete('/resource-documents/{resourceDocument}', [AdminResourceDocumentController::class, 'destroy'])->name('resource-documents.destroy');
        Route::post('/resource-documents/reorder', [AdminResourceDocumentController::class, 'reorder'])->name('resource-documents.reorder');
        
        // 資料カテゴリー（管理画面側）
        Route::post('/resource-document-categories', [AdminResourceDocumentCategoryController::class, 'store'])->name('resource-document-categories.store');
        Route::put('/resource-document-categories/{resourceDocumentCategory}', [AdminResourceDocumentCategoryController::class, 'update'])->name('resource-document-categories.update');
        Route::delete('/resource-document-categories/{resourceDocumentCategory}', [AdminResourceDocumentCategoryController::class, 'destroy'])->name('resource-document-categories.destroy');
        Route::post('/resource-document-categories/reorder', [AdminResourceDocumentCategoryController::class, 'reorder'])->name('resource-document-categories.reorder');
        // ──────────────────────────────────────────
        // e-ラーニング問題管理（管理画面側）※admin.プレフィックスの既存グループに追加
        // ──────────────────────────────────────────
        Route::get('/elearning-questions', [AdminElearningQuestionController::class, 'index'])->name('elearning-questions.index');
        Route::post('/elearning-questions', [AdminElearningQuestionController::class, 'store'])->name('elearning-questions.store');
        Route::put('/elearning-questions/{elearningQuestion}', [AdminElearningQuestionController::class, 'update'])->name('elearning-questions.update');
        Route::delete('/elearning-questions/{elearningQuestion}', [AdminElearningQuestionController::class, 'destroy'])->name('elearning-questions.destroy');
        Route::post('/elearning-questions/{elearningQuestion}/toggle-active', [AdminElearningQuestionController::class, 'toggleActive'])->name('elearning-questions.toggle-active');
        
        // e-ラーニング受験結果（管理画面側）
        Route::get('/elearning-attempts', [AdminElearningAttemptController::class, 'index'])->name('elearning-attempts.index');
        Route::get('/elearning-attempts/organizations/{organization}', [AdminElearningAttemptController::class, 'byOrganization'])->name('elearning-attempts.by-organization');
        // ──────────────────────────────────────────
        // プロフィール変更履歴（管理画面側）※admin.プレフィックスの既存グループに追加
        // ──────────────────────────────────────────
        Route::get('/profile-change-logs', [AdminProfileChangeLogController::class, 'index'])->name('profile-change-logs.index');

        Route::get('member-name-matches', [AdminMemberNameMatchController::class, 'index'])
            ->name('member-name-matches.index');
        Route::post('member-name-matches/{candidate}/confirm', [AdminMemberNameMatchController::class, 'confirm'])
            ->name('member-name-matches.confirm');
        Route::post('member-name-matches/{candidate}/reject', [AdminMemberNameMatchController::class, 'reject'])
            ->name('member-name-matches.reject');

        Route::prefix('member')->name('member.')->group(function () {

            Route::get('/', [AdminMemberController::class, 'index'])->name('index');
                Route::get('/check-name-match', [AdminMemberController::class, 'checkNameMatch'])->name('admin.members.check-name-match');
                Route::get('/pdf/{id}', [AdminMemberController::class, 'pdfPreview'])->name('pdf.preview');
                Route::get('/{member}', [AdminMemberController::class, 'show'])->name('show');
                Route::get('/{member}/edit', [AdminMemberController::class, 'edit'])->name('edit');
                Route::put('/{member}', [AdminMemberController::class, 'update'])->name('update');
                // routes/admin
                Route::get('{member}/status/edit', [AdminMemberController::class, 'editStatus'])
                    ->name('editStatus');
                Route::post('{member}/upgrade-tier', [AdminMemberController::class, 'upgradeTier'])
                    ->name('upgrade-tier');
                Route::post('{member}/tier/downgrade', [AdminMemberController::class, 'downgradeTier'])
                    ->name('tier.downgrade');
                Route::post('{member}/downgrade-tier', [AdminMemberController::class, 'downgradeTier'])
                    ->name('downgrade-tier');
 
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

Route::get('/debug-secure', function () {
    return [
        'secure' => request()->secure(),
        'url' => request()->fullUrl(),
        'scheme' => request()->getScheme(),
    ];
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

Route::get('/insurance-simulation', function () {
    return Inertia::render('InsuranceSimulation');
});

Route::post('/locale', function (Request $request) {
    $locale = $request->input('locale', 'en');
    session(['locale' => $locale]);
    app()->setLocale($locale);
    return response()->json(['status' => 'ok']);
});

Route::get('elearning-invitations/{token}', [ElearningInvitationController::class, 'show'])
    ->name('elearning-invitations.show');

Route::post('elearning-invitations/{token}/submit', [ElearningInvitationController::class, 'submit'])
    ->name('elearning-invitations.submit');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 症例報告
    Route::resource('reports', App\Http\Controllers\CaseReportController::class)
        ->only(['index', 'create', 'store']);
    // ──────────────────────────────────────────
    // 手技動画（My Page）
    // ──────────────────────────────────────────
    Route::get('/procedure-videos', [ProcedureVideoController::class, 'index'])->name('procedure-videos.index');
    Route::post('/procedure-videos/presign', [ProcedureVideoController::class, 'presign'])->name('procedure-videos.presign');
    Route::post('/procedure-videos', [ProcedureVideoController::class, 'store'])->name('procedure-videos.store');
    // ──────────────────────────────────────────
    // 参考動画（My Page側）※認証ミドルウェア配下の既存グループに追加
    // ──────────────────────────────────────────
    Route::get('/reference-videos', [ReferenceVideoController::class, 'index'])->name('reference-videos.index');
    Route::post('/reference-videos/{referenceVideo}/mark-watched', [ReferenceVideoController::class, 'markWatched'])->name('reference-videos.mark-watched');

    // ──────────────────────────────────────────
    // 資料（My Page側）※認証ミドルウェア配下の既存グループに追加
    // ──────────────────────────────────────────
    Route::get('/resource-documents', [ResourceDocumentController::class, 'index'])->name('resource-documents.index');
    // ──────────────────────────────────────────
    // ライセンス証（My Page側）※認証ミドルウェア配下の既存グループに追加
    // ──────────────────────────────────────────
    Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');

    // ──────────────────────────────────────────
    // e-ラーニング（My Page側）※認証ミドルウェア配下の既存グループに追加
    // ──────────────────────────────────────────
    Route::get('/elearning', [ElearningController::class, 'index'])->name('elearning.index');
    Route::get('/elearning/history', [ElearningController::class, 'history'])->name('elearning.history');
    Route::post('/elearning/start', [ElearningController::class, 'start'])->name('elearning.start');
    Route::get('/elearning/{attempt}', [ElearningController::class, 'show'])->name('elearning.show');
    Route::post('/elearning/{attempt}/submit', [ElearningController::class, 'submit'])->name('elearning.submit');
    Route::get('/elearning/{attempt}/result', [ElearningController::class, 'result'])->name('elearning.result');
    // ──────────────────────────────────────────
    // プロフィール編集（My Page側）※認証ミドルウェア配下の既存グループに追加
    // ──────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/organization', [ProfileController::class, 'updateOrganization'])->name('profile.organization.update');
    Route::put('/profile/member', [ProfileController::class, 'updateMember'])->name('profile.member.update');
    Route::put('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');


});

Route::middleware('guest')->group(function () {
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->name('password.update');
});
