<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\User; // ← これを追加！

class ProcessController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Process::query();
        // テナント絞り込み（Super Admin は全件表示）
        if (!$user->hasRole('Super Admin')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        if ($name = $request->input('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        // ソート
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // ページあたり件数
        $perPage = intval($request->input('per_page', 20));


        $processes = Process::query()
            ->when($request->name, fn($q,$v)=>$q->where('name','like',"%$v%"))
            ->orderBy($request->sort_by ?? 'id', $request->sort_dir ?? 'asc')
            ->paginate($perPage)
            ->withQueryString(); // 検索条件をページリンクに保持


        return Inertia::render('Processes/Index', [
            'processes' => $processes,
            'user' => $user, // Vue 側で判定に必要
            'filters' => $request->only(['name','per_page','sort_by','sort_dir']),
        ]);
    }

    // Create 画面
    public function create(Request $request)
    {
        $process = null;

        $user = auth()->user()->load('roles');

        // コピー用モードの場合
        if ($request->input('mode') === 'copy' && $process_id = $request->input('process_id')) {
            $process = Process::find($process_id);
        }

        return Inertia::render('Processes/Create', [
            'filters' => $request->only(['name','per_page','sort_by','sort_dir','page']),
            'process' => $process, // コピー元のデータを渡す
            'user' => $user, // Vue 側で判定に必要
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string'],
        ], [
            'name.required' => __('validation.required', ['attribute' => __('Name')]),
        ]);

        Process::create($validated);

        return redirect()->route('processes.index', $request->only(['name','per_page','sort_by','sort_dir','page']))
            ->with('success', __('process has been created.'));
    }

    public function edit(Request $request, Process $process)
    {
        $user = $request->user();

        return Inertia::render('Processes/Edit', [
            'process' => $process,
            'user' => $user, // Vue 側で判定に必要
            'filters' => $request->only(['name','per_page','sort_by','sort_dir','page'])
        ]);
    }

    public function update(Request $request, Process $process)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string'],
        ], [
            'name.required' => __('validation.required', ['attribute' => __('Name')]),
        ]);

        $process->update($validated);

        return redirect()->route('processes.index', $request->only(['name','per_page','sort_by','sort_dir','page']))
            ->with('success', __('process has been updated.'));
    }

    public function destroy(Process $process)
    {
        $process->delete();
        return redirect()->route('processes.index')->with('success', __('process has been deleted.'));
    }

    public function bulkDelete(Request $request)
    {
        Process::whereIn('id', $request->ids)->delete();
        return redirect()->route('processes.index')->with('success', __('Selected processes have been deleted.'));
    }
}