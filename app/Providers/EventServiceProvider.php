<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Application;
use App\Observers\ApplicationObserver;
use App\Models\Member;
use App\Observers\MemberObserver;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Application::observe(ApplicationObserver::class);

        Member::observe(MemberObserver::class);
    }
}
