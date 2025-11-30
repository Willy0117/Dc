<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditConference;
use App\Models\CreditCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditConferenceController extends Controller
{
   public function index()
    {
        $conferences = CreditConference::with('category')->get();
        return Inertia::render('Admin/CreditConferences/Index', [
            'conferences' => $conferences,
            'categories' => CreditCategory::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/CreditConferences/Edit', [
            'categories' => CreditCategory::all()
        ]);
    }

    public function edit(CreditConference $creditConference)
    {
        return Inertia::render('Admin/CreditConferences/Edit', [
            'conference' => $creditConference,
            'categories' => CreditCategory::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'credit_category_id' => 'required|exists:credit_categories,id'
        ]);

        CreditConference::create($request->only(['name','credit_category_id']));

        return redirect()->route('admin.credit-conferences.index')->with('success', __('Credit Conference created.'));
    }

    public function update(Request $request, CreditConference $creditConference)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'credit_category_id' => 'required|exists:credit_categories,id'
        ]);

        $creditConference->update($request->only(['name','credit_category_id']));

        return redirect()->route('admin.credit-conferences.index')->with('success', __('Credit Conference updated.'));
    }

    public function destroy(CreditConference $creditConference)
    {
        $creditConference->delete();
        return redirect()->route('admin.credit-conferences.index')->with('success', __('Credit Conference deleted.'));
    }
}

