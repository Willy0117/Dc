<?php

namespace App\Providers;

use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;
use App\Models\Member;
use App\Observers\MemberObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       //
       if (env('APP_ENV') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
       }
       Inertia::share([
            'csrf_token' => fn () => csrf_token(),
       ]);

       // 追加：先生の新規登録時、氏名一致の既存memberを自動検知する（変更点3）
       Member::observe(MemberObserver::class);      
       
    }
}
