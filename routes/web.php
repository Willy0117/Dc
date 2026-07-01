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
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\WebhookLogController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\StripeController;
use App\Http\Controllers\Admin\LicenseFeeController;

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\StripeWebhookController;

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

        Route::prefix('member')->name('member.')->group(function () {

        Route::get('/', [AdminMemberController::class, 'index'])->name('index');
            Route::get('/pdf/{id}', [AdminMemberController::class, 'pdfPreview'])->name('pdf.preview');
            Route::get('/{member}', [AdminMemberController::class, 'show'])->name('show');
            Route::get('/{member}/edit', [AdminMemberController::class, 'edit'])->name('edit');
            Route::put('/{member}', [AdminMemberController::class, 'update'])->name('update');
            // routes/admin
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

Route::middleware([
    'auth:web',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
