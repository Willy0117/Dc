<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saas;
use Inertia\Inertia;

class SaasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $saasList = Saas::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Saas/Index', [
            'saasList' => $saasList,
        ]);        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       return Inertia::render('Admin/Saas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        Saas::create($request->all());

        return redirect()->route('saas.index')->with('success', 'SaaS情報を登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Saas $saas)
    {
        return Inertia::render('Admin/Saas/Edit', [
            'saas' => $saas,
        ]);
    }
    public function update(Request $request, Saas $saas)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $saas->update($request->all());

        return redirect()->route('saas.index')->with('success', 'SaaS情報を更新しました。');
    }

    public function destroy(Saas $saas)
    {
        $saas->delete();

        return redirect()->route('saas.index')->with('success', 'SaaS情報を削除しました。');
    }
}
