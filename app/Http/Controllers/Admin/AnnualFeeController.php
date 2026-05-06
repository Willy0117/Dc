<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
 
        $fees = AnnualFee::with('member')
            ->orderByDesc('fiscal_year')
            ->get();

        return inertia('Admin/AnnualFees/Index', [
            'fees' => $fees,
        ]);
    }

}