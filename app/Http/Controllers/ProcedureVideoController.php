<?php

namespace App\Http\Controllers;

use App\Models\ProcedureVideo;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProcedureVideoController extends Controller
{
    public function __construct(private FileService $fileService) {}

    // ──────────────────────────────────────────
    // ログイン中のユーザーから所属organization_idを取得
    // 変更点：病院ログイン(type=1)はuser->organization_idに直接あるが、
    // 先生ログイン(type=2)はuser->member->organization_idを経由する必要がある。
    // ──────────────────────────────────────────
    private function currentOrganizationId(Request $request): ?int
    {
        $user = $request->user();
        return match ((int) $user->type) {
            1 => $user->organization_id,
            2 => $user->member?->organization_id,
            default => null,
        };
    }

    // ──────────────────────────────────────────
    // 一覧（アップロード証跡）
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $organizationId = $this->currentOrganizationId($request);

        $videos = ProcedureVideo::with('member')
            ->where('organization_id', $organizationId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($v) => [
                'id'            => $v->id,
                'title'         => $v->title,
                'member_name'   => $v->member?->full_name,
                'file_url'      => $this->fileService->getUrl($v->file_path),
                'thumbnail_url' => $v->thumbnail_path ? $this->fileService->getUrl($v->thumbnail_path) : null,
                'file_size'     => $v->file_size,
                'created_at'    => $v->created_at->format('Y-m-d H:i'),
            ]);

        $members = \App\Models\Member::where('organization_id', $organizationId)
            ->orderBy('last_name')
            ->get(['id', 'last_name', 'first_name']);

        return Inertia::render('ProcedureVideos/Index', [
            'videos'  => $videos,
            'members' => $members,
        ]);
    }

    // ──────────────────────────────────────────
    // Step1: presigned URL発行
    // ──────────────────────────────────────────
    public function presign(Request $request)
    {
        $request->validate([
            'filename'  => 'required|string|max:255',
            'file_size' => 'required|integer|min:1|max:524288000',
            'kind'      => 'nullable|in:video,thumbnail',
        ]);

        $kind = $request->input('kind', 'video');
        $extension = pathinfo($request->filename, PATHINFO_EXTENSION);
        $dir = $kind === 'thumbnail' ? 'procedure_videos/thumbnails' : 'procedure_videos';
        $key = $dir . '/' . date('Y/m') . '/' . Str::uuid() . '.' . $extension;

        $uploadUrl = Storage::disk('s3')->temporaryUploadUrl(
            $key,
            now()->addMinutes(30),
            ['ContentType' => $request->input('content_type', 'video/mp4')]
        );

        return response()->json([
            'upload_url' => $uploadUrl['url'],
            'headers'    => $uploadUrl['headers'],
            'key'        => $key,
        ]);
    }

    // ──────────────────────────────────────────
    // Step2: アップロード完了後、DBレコード作成
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'nullable|string|max:255',
            'member_id'     => 'nullable|exists:members,id',
            'key'           => 'required|string',
            'thumbnail_key' => 'nullable|string',
            'file_size'     => 'required|integer|max:524288000',
        ]);

        if (!Storage::disk('s3')->exists($request->key)) {
            return response()->json(['message' => 'アップロードが確認できませんでした。'], 422);
        }

        ProcedureVideo::create([
            'organization_id' => $this->currentOrganizationId($request),
            'member_id'       => $request->member_id,
            'title'           => $request->title,
            'file_path'       => $request->key,
            'thumbnail_path'  => $request->thumbnail_key,
            'file_size'       => $request->file_size,
            'uploaded_by'     => $request->user()->id,
        ]);

        return response()->json(['message' => 'アップロードしました。']);
    }
}