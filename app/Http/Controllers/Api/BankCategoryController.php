<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankCategory;

class BankCategoryController extends Controller
{
    public function index()
    {
        // idとnameだけ返す
        return BankCategory::orderBy('id')->get(['id','bank_name']);
    }
}