<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Credit;
use App\Models\CreditCategory;
use App\Models\CreditConference;
use App\Models\CreditRole;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditController extends Controller
{
    public function index()
    {
        $credits = Credit::with(['category','conference','role'])->get();
        return Inertia::render('Admin/Credits/Index', [
            'credits' => $credits
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Credits/Create', [
            'categories' => CreditCategory::all(),
            'conferences' => CreditConference::all(),
            'roles' => CreditRole::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'credit_category_id' => 'required|exists:credit_categories,id',
            'credit_conference_id' => 'required|exists:credit_conferences,id',
            'credit_role_id' => 'required|exists:credit_roles,id',
            'credit' => 'required|integer|min:0',
        ]);

        Credit::create($request->all());

        return redirect()->route('admin.credits.index')
            ->with('success', 'Credit created.');
    }

    public function edit(Credit $credit)
    {
        return Inertia::render('Admin/Credits/Edit', [
            'credit' => $credit,
            'categories' => CreditCategory::all(),
            'conferences' => CreditConference::all(),
            'roles' => CreditRole::all(),
        ]);
    }

    public function update(Request $request, Credit $credit)
    {
        $request->validate([
            'credit_category_id' => 'required|exists:credit_categories,id',
            'credit_conference_id' => 'required|exists:credit_conferences,id',
            'credit_role_id' => 'required|exists:credit_roles,id',
            'credit' => 'required|integer|min:0',
        ]);

        $credit->update($request->all());

        return redirect()->route('admin.credits.index')
            ->with('success', 'Credit updated.');
    }

    public function destroy(Credit $credit)
    {
        $credit->delete();
        return redirect()->route('admin.credits.index')
            ->with('success', 'Credit deleted.');
    }
}

