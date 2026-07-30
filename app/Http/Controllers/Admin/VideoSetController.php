<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoSet;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class VideoSetController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:video-sets.view', only: ['index', 'edit']),
            new Middleware('can:video-sets.create', only: ['create', 'store']),
            new Middleware('can:video-sets.update', only: ['update']),
            new Middleware('can:video-sets.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $perPage = (int) $request->input('per_page', 20);

        $allowedSorts = ['id', 'name', 'price_jpy', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        $videoSets = VideoSet::forTenant($request->user()->tenant_id ?? null)
            ->withCount(['videos', 'orders'])
            ->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/VideoSets/Index', [
            'videoSets' => $videoSets,
            'filters' => [
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/VideoSets/Edit', [
            'videoSet' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['tenant_id'] = $request->user()->tenant_id ?? null;

        $videoSet = VideoSet::create($data);

        return redirect()
            ->route('admin.video-sets.edit', $videoSet)
            ->with('success', '動画セットを作成しました。続けて動画・テスト問題を登録してください。');
    }

    public function edit(Request $request, VideoSet $videoSet)
    {
        $videoSet->load(['videos.questions.choices']);

        return Inertia::render('Admin/VideoSets/Edit', [
            'videoSet' => $videoSet,
        ]);
    }

    public function update(Request $request, VideoSet $videoSet)
    {
        $videoSet->update($this->validated($request));

        return back()->with('success', '更新しました。');
    }

    public function destroy(VideoSet $videoSet)
    {
        $videoSet->delete();

        return redirect()->route('admin.video-sets.index')->with('success', '削除しました。');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'theme' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_jpy' => ['required', 'integer', 'min:0'],
            'stripe_price_id' => ['required', 'string', 'max:255'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'active' => ['boolean'],
        ]);
    }
}
