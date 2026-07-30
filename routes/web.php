<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetLocaleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\WebhookLogController;
use App\Http\Controllers\Admin\StripeController;
use App\Http\Controllers\Admin\StorageController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\VideoSetController;

use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\ProductController;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ViewingController;
use App\Http\Controllers\CertificateDownloadController;
use App\Http\Controllers\VideoDownloadController;

// ── 商品ページ・決済 ─────────────────────────
Route::post('/products/{videoSet}/checkout', [ProductController::class, 'checkout'])->name('products.checkout');

Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

// ── Stripe Webhook（CSRF除外が必要。bootstrap/app.php or VerifyCsrfToken参照）
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// ── 証明書デザイン確認用（購入・視聴・テストを経由せずPDFを直接プレビュー）
// 本番公開後は削除するか、認証必須に変更することを推奨
Route::get('/dev/certificate-preview/{video}', [App\Http\Controllers\CertificatePreviewController::class, 'show'])->name('dev.certificate.preview');


// ── 視聴サイト（token付きURL・ミドルウェアで認証）─────
// テスト・証明書は「動画(講義)単位」。1講義＝1テスト＝1証明書。
Route::middleware('order.token')->group(function () {
    Route::get('/watch/{token}', [ViewingController::class, 'index'])->name('watch.index');
    Route::post('/watch/{token}/complete', [ViewingController::class, 'markComplete'])->name('watch.complete');

    Route::get('/watch/{token}/quiz/{video}', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('/watch/{token}/quiz/{video}/answer', [QuizController::class, 'answer'])->name('quiz.answer');

    Route::get('/watch/{token}/certificate/{video}/download', [CertificateDownloadController::class, 'download'])->name('certificate.download');

    // 視聴完了済みの動画のみダウンロード可能
    Route::get('/watch/{token}/video/{video}/download', [VideoDownloadController::class, 'download'])->name('video.download');

    Route::get('/watch/{token}/video/{video}/material', [VideoDownloadController::class, 'material'])->name('video.material');
});


Route::get('/', [ProductController::class, 'index'])->name('products.index');


Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {

        Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');

        Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.post');
    });

    // 認証後
    Route::middleware(['auth:admin'])->group(function () {

        Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])
            ->name('logout');

        Route::get('/dashboard', [OrderController::class, 'dashboard'])->name('dashboard');

//        Route::get('/dashboard', fn () => inertia('Admin/Dashboard'))
//            ->name('dashboard');
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
            Route::resource('video-sets', VideoSetController::class)
        ->except(['show']); // 一覧・作成・編集・更新・削除

        Route::post('video-sets/{videoSet}/videos', [VideoController::class, 'store'])->name('video-sets.videos.store');
        Route::put('video-sets/{videoSet}/videos/{video}', [VideoController::class, 'update'])->name('video-sets.videos.update');
        Route::delete('video-sets/{videoSet}/videos/{video}', [VideoController::class, 'destroy'])->name('video-sets.videos.destroy');

        Route::post('video-sets/{videoSet}/videos/{video}/questions', [QuestionController::class, 'store'])->name('video-sets.videos.questions.store');
        Route::put('video-sets/{videoSet}/videos/{video}/questions/{question}', [QuestionController::class, 'update'])->name('video-sets.videos.questions.update');
        Route::delete('video-sets/{videoSet}/videos/{video}/questions/{question}', [QuestionController::class, 'destroy'])->name('video-sets.videos.questions.destroy');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
        // admin
        Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class);
        //        Route::resource('organizations', \App\Http\Controllers\Admin\OrganizationController::class);
        Route::get('webhook-logs/unread-count', [WebhookLogController::class, 'unreadCount'])->name('webhook_logs.unread_count');
        Route::get('webhook-logs', [WebhookLogController::class, 'index'])->name('webhook_logs.index');
        Route::post('webhook-logs/mark-all-read', [WebhookLogController::class, 'markAllRead'])->name('webhook_logs.mark_all_read');
        
        // ──────────────────────────────────────────────────────────────
        // Stripe 管理
        // ──────────────────────────────────────────────────────────────
        Route::get('stripe',
            [StripeController::class, 'index']
        )->name('stripe.index');
        
        Route::post('stripe/payment-link',[StripeController::class, 'paymentLink'])->name('stripe.payment-link');

        Route::post('stripe/{invoice}/resend-email', [\App\Http\Controllers\Admin\StripeController::class, 'resendEmail'])->name('stripe.resendEmail');

        Route::get('storage', [\App\Http\Controllers\Admin\StorageController::class, 'index'])->name('storage.index');

    });
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
