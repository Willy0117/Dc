<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\ProcedureVideo;
use App\Services\FileService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProcedureVideoController extends Controller
{
    public function __construct(private FileService $fileService) {}

    public function index(Request $request)
    {
        $videos = ProcedureVideo::with('organization', 'member')
            ->when($request->organization_id && $request->organization_id !== 'all',
                fn($q, $id) => $q->where('organization_id', $id)
            )
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $videos->getCollection()->transform(fn($v) => [
            'id'                => $v->id,
            'title'             => $v->title,
            'member_name'       => $v->member?->full_name,
            'file_url'          => $this->fileService->getUrl($v->file_path),
            'thumbnail_url'     => $v->thumbnail_path ? $this->fileService->getUrl($v->thumbnail_path) : null,
            'file_size'         => $v->file_size,
            'organization_name' => $v->organization?->name,
            'created_at'        => $v->created_at->format('Y-m-d H:i'),
        ]);

        return Inertia::render('Admin/ProcedureVideos/Index', [
            'videos'              => $videos,
            'filters'             => $request->only(['organization_id']),
            'organizationOptions' => Organization::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    // ──────────────────────────────────────────
    // 削除（S3実ファイルも削除）
    // ──────────────────────────────────────────
    public function destroy(ProcedureVideo $procedureVideo)
    {
        $this->fileService->delete($procedureVideo->file_path);
        $procedureVideo->delete();

        return redirect()->back()->with('success', '動画を削除しました。');
    }
}