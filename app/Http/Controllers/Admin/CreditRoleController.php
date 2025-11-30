<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditRole;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditRoleController extends Controller
{
    public function index()
    {
        $roles = CreditRole::all();
        return Inertia::render('Admin/CreditRoles/Index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/CreditRoles/Create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        CreditRole::create($request->all());

        return redirect()->route('admin.credit-roles.index')
            ->with('success', 'Credit Role created.');
    }

    public function edit(CreditRole $creditRole)
    {
        return Inertia::render('Admin/CreditRoles/Edit', [
            'role' => $creditRole
        ]);
    }

    public function update(Request $request, CreditRole $creditRole)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $creditRole->update($request->all());

        return redirect()->route('admin.credit-roles.index')
            ->with('success', 'Credit Role updated.');
    }

    public function destroy(CreditRole $creditRole)
    {
        $creditRole->delete();
        return redirect()->route('admin.credit-roles.index')
            ->with('success', 'Credit Role deleted.');
    }
}

