<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\Application;
use App\Observers\ApplicationObserver;
use App\Models\Member;
use App\Observers\MemberObserver;
use App\Listeners\RecordPasswordSetAt;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Application::observe(ApplicationObserver::class);

        Member::observe(MemberObserver::class);

        Event::listen(PasswordReset::class, RecordPasswordSetAt::class);
    }
}
