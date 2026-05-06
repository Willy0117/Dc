<?php

namespace App\Observers;

use App\Models\Member;
use App\Models\OperationLog;

class MemberObserver
{
    /**
     * Handle the Member "created" event.
     */
    public function created(Member $member): void
    {
        //
    }

    /**
     * Handle the Member "updated" event.
     */
    public function updated(Member $member): void
    {
        try {
            OperationLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'before' => $model->getOriginal(),
                'after' => $model->getChanges(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('OperationLog failed', [
                'message' => $e->getMessage(),
            ]);
        }
        //
    }
    /**
     * Handle the Member "deleted" event.
     */
    public function deleted(Member $member): void
    {
        //
    }

    /**
     * Handle the Member "restored" event.
     */
    public function restored(Member $member): void
    {
        //
    }

    /**
     * Handle the Member "force deleted" event.
     */
    public function forceDeleted(Member $member): void
    {
        //
    }
}
