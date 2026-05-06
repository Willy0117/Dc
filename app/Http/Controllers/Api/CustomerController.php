<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->query('keyword', '');

        $customers = Customer::where('company_name', 'like', "%{$keyword}%")
            ->limit(10)
            ->get(['id', 'company_name']);

        return response()->json($customers);
    }
}
