<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;
use App\Models\AnnualFee;

use Illuminate\Validation\Rule;

use App\Services\FileService;

class AnnualFeeController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();

        $fees = AnnualFee::with('member')
            ->where('member_id', $user->member_id)
            ->orderByDesc('fiscal_year')
            ->get();

        return inertia('AnnualFees/Index', [
            'fees' => $fees,
        ]);
    }

}