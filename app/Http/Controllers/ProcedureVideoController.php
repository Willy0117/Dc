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
    // 一覧（アップロード証跡）
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $organizationId = $request->user()->organization_id;

        $videos = ProcedureVideo::where('organization_id', $organizationId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($v) => [
                'id'         => $v->id,
                'title'      => $v->title,
                'file_url'   => $this->fileService->getUrl($v->file_path),
                'file_size'  => $v->file_size,
                'created_at' => $v->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('ProcedureVideos/Index', [
            'videos' => $videos,
        ]);
    }

    // ──────────────────────────────────────────
    // Step1: presigned URL発行
    // ──────────────────────────────────────────
    public function presign(Request $request)
    {
        $request->validate([
            'filename'  => 'required|string|max:255',
            'file_size' => 'required|integer|min:1|max:524288000', // TODO: 上限確定後に調整（現在は仮で500MB）
        ]);

        $extension = pathinfo($request->filename, PATHINFO_EXTENSION);
        $key = 'procedure_videos/' . date('Y/m') . '/' . Str::uuid() . '.' . $extension;

        $uploadUrl = Storage::disk('s3')->temporaryUploadUrl(
            $key,
            now()->addMinutes(15),
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
            'title'     => 'nullable|string|max:255',
            'key'       => 'required|string',
            'file_size' => 'required|integer',
        ]);

        if (!Storage::disk('s3')->exists($request->key)) {
            return response()->json(['message' => 'アップロードが確認できませんでした。'], 422);
        }

        ProcedureVideo::create([
            'organization_id' => $request->user()->organization_id,
            'title'           => $request->title,
            'file_path'       => $request->key,
            'file_size'       => $request->file_size,
            'uploaded_by'     => $request->user()->id,
        ]);

        return redirect()->back()->with('success', '動画をアップロードしました。');
    }
}