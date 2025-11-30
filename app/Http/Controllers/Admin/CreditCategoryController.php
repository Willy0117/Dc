<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditCategoryController extends Controller
{
    public function index()
    {
        $categories = CreditCategory::all();
        return Inertia::render('Admin/CreditCategories/Index', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/CreditCategories/Create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        CreditCategory::create($request->all());

        return redirect()->route('admin.credit-categories.index')
            ->with('success', 'Credit Category created.');
    }

    public function edit(CreditCategory $creditCategory)
    {
        return Inertia::render('Admin/CreditCategories/Edit', [
            'category' => $creditCategory
        ]);
    }

    public function update(Request $request, CreditCategory $creditCategory)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $creditCategory->update($request->all());

        return redirect()->route('admin.credit-categories.index')
            ->with('success', 'Credit Category updated.');
    }

    public function destroy(CreditCategory $creditCategory)
    {
        $creditCategory->delete();
        return redirect()->route('admin.credit-categories.index')
            ->with('success', 'Credit Category deleted.');
    }
}

