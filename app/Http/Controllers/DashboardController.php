<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RehabApplication;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $applications = RehabApplication::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'user' => $user, 
            'applications' => $applications,
        ]);
    }
}
