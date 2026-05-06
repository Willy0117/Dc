<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\MemberImport;
use Maatwebsite\Excel\Facades\Excel;

class MemberImportController extends Controller
{
    // 一覧（またはインポート画面への入口）
    public function index()
    {
        return inertia('Admin/Members/Import');
    }

    // 既存：インポート実行
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(
            new \App\Imports\MemberImport,
            $request->file('file')
        );

        return back()->with('success', 'インポート完了');
    }
}