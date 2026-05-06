<?php

namespace App\Providers;

use Inertia\Inertia;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            LogoutResponse::class,
            CustomLogoutResponse::class
        );
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);
        // リクエストURLに基づいて、動的に使用するガードを書き換える
//        if (request()->is('admin/*')) {
//            config(['fortify.guard' => 'admin']);
//        }
        $guard = request()->is('admin', 'admin/*') ? 'admin' : 'web';
        config(['fortify.guard' => $guard]);

        // 管理者専用のログインページを表示
        Fortify::loginView(function () {
            if (request()->is('admin/login')) {
                return Inertia::render('Admin/Login'); // コピーしたVue
            }
            return Inertia::render('Auth/Login');
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });


        Fortify::authenticateUsing(function ($request) {
            $guard = request()->is('admin/*') ? 'admin' : 'web';
            config(['fortify.guard' => $guard]);

            if ($guard === 'admin') {
                $request->validate([
                    'email' => 'required|email',
                    'password' => 'required|string',
                ]);
                $user = \App\Models\Admin::where('email', $request->email)->first();
            } else {
                $request->validate([
                    'username' => 'required|string',
                    'password' => 'required|string',
                ]);
                $user = \App\Models\User::where('username', $request->username)
                                        ->orWhere('email', $request->username)
                                        ->first();
            }

            if ($user && Hash::check($request->password, $user->password)) {
                // SPA ではここで明示的に guard でログイン
                Auth::guard($guard)->login($user, $request->filled('remember'));
                return $user;
            }

            return null;
        });

        Fortify::redirects('login', function () {
            if (Auth::guard('admin')->check()) {
                return '/admin/dashboard';
            }
            if (Auth::guard('web')->check()) {
                return '/dashboard';
            }
            return route('login');
        });        

    }
}
