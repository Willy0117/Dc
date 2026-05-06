<?php

namespace App\Observers;

use App\Models\Application;
use App\Models\OperationLog;

class ApplicationObserver
{
    /**
     * Handle the Application "created" event.
     */
    public function created(Application $application): void
    {
        try {
            OperationLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'model_type' => get_class($application),
                'model_id' => $application->id,
                'before' => $application->getOriginal(),
                'after' => $application->getChanges(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('OperationLog failed', ['msg' => $e->getMessage()]);
        }          //
    }

    /**
     * Handle the Application "updated" event.
     */
    public function updated(Application $application): void
    {
        try {
            OperationLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => get_class($application),
                'model_id' => $application->id,
                'before' => $application->getOriginal(),
                'after' => $application->getChanges(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('OperationLog failed', ['msg' => $e->getMessage()]);
        }       //
    }

    /**
     * Handle the Application "deleted" event.
     */
    public function deleted(Application $application): void
    {
        OperationLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($model),
            'model_id' => $model->id,
        ]);        //
    }

    /**
     * Handle the Application "restored" event.
     */
    public function restored(Application $application): void
    {
        //
    }

    /**
     * Handle the Application "force deleted" event.
     */
    public function forceDeleted(Application $application): void
    {
        //
    }
}
