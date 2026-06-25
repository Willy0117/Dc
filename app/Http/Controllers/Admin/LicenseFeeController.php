<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseFeeMaster;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LicenseFeeController extends Controller
{
    public function index()
    {
        $masters = LicenseFeeMaster::orderByDesc('started_at')->get();

        return Inertia::render('Admin/LicenseFees/Index', [
            'masters' => $masters,
            'current' => LicenseFeeMaster::current(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'corporate_fee' => ['required', 'integer', 'min:0'],
            'personal_fee'  => ['required', 'integer', 'min:0'],
            'started_at'    => ['required', 'date'],
        ]);

        LicenseFeeMaster::create($request->only(['corporate_fee', 'personal_fee', 'started_at']));

        return back()->with('success', '料金を登録しました。');
    }
}
