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

        $applications = array();

        return Inertia::render('Dashboard', [
            'user' => $user, 
            'applications' => $applications,
        ]);
    }
}
