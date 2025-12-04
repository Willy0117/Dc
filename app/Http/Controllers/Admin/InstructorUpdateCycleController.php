<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstructorUpdateCycle;
use Illuminate\Http\Request;

class InstructorUpdateCycleController extends Controller
{
    public function review(Request $request, InstructorUpdateCycle $cycle)
    {
        $request->validate([
            'status' => 'required|in:updated,no_update',
            'reason' => 'nullable|string|max:1000',
        ]);

        $cycle->status = $request->status;
        $cycle->reason = $request->reason ?? null;
        $cycle->save();

        return redirect()->back()->with('success', __('Review updated successfully.'));
    }
}
