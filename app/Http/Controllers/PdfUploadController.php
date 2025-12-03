<?php

namespace App\Http\Controllers;

use App\Models\PdfUpload;
use App\Models\CreditCategory;
use App\Models\CreditConference;
use App\Models\CreditRolePoint;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PdfUploadController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $member = Member::where('user_id', $user->id)->first();

        // アップロード一覧
        $uploads = PdfUpload::with(['creditCategory', 'creditConference', 'creditRole'])
            ->where('member_id', $member->id)
            ->latest()
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'credit_category_name' => $u->creditCategory?->name,
                'credit_conference_name' => $u->creditConference?->name,
                'role_name' => $u->creditRole?->role,
                'points' => $u->point,
                'status' => $u->status,
                'thumbnail_path' => $u->thumbnail_path,
                'rejection_message' => $u->rejection_message,
            ]);

        // 全カテゴリー
        $creditCategories = CreditCategory::all();

        // 全学術集会・論文・セミナー等
        $conferences = CreditConference::all();

        // 全 roles
        $roles = CreditRolePoint::all()->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->role,
            'points' => $r->points,
            'credit_category_id' => $r->credit_category_id,
            'credit_conference_id' => $r->credit_conference_id,
        ]);

        return inertia('PdfUploads/Index', compact('uploads', 'creditCategories', 'conferences', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
            'credit_category_id' => 'required|exists:credit_categories,id',
            'credit_conference_id' => 'required|exists:credit_conferences,id',
            'role_id' => 'required|exists:credit_role_points,id',
            'session' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $member = Member::where('user_id', $user->id)->first();

        if (!Storage::disk('private')->exists('pdf_uploads')) {
            Storage::disk('private')->makeDirectory('pdf_uploads');
        }

        $path = $request->file('file')->store('pdf_uploads', 'private');

        // role_id でポイントを取得
        $role = CreditRolePoint::find($request->role_id);
        $points = $role ? $role->points : 0;

        $upload = PdfUpload::create([
            'member_id' => $member->id,
            'file_path' => $path,
            'credit_category_id' => $request->credit_category_id,
            'credit_conference_id' => $request->credit_conference_id,
            'credit_role_id' => $request->role_id,
            'session' => $request->session ?? '',
            'points' => $points,
            'status' => 'pending',
        ]);

        // サムネイル生成
        try {
            if (!Storage::disk('private')->exists('thumbnails')) {
                Storage::disk('private')->makeDirectory('thumbnails');
            }

            $pdfPath = storage_path('app/private/' . $path);
            $thumbnailPath = storage_path('app/private/thumbnails/' . basename($path, '.pdf') . '.png');

            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($pdfPath . '[0]');
            $imagick->setImageFormat('png');
            $imagick->writeImage($thumbnailPath);
            $imagick->clear();
            $imagick->destroy();

            $upload->update(['thumbnail_path' => 'thumbnails/' . basename($path, '.pdf') . '.png']);
        } catch (\Exception $e) {
            \Log::error('Thumbnail generation failed: ' . $e->getMessage());
        }

        return back()->with('success', __('PDF uploaded successfully.'));
    }

    public function view(PdfUpload $pdf)
    {
        $filePath = storage_path('app/private/' . $pdf->file_path);
        if (!file_exists($filePath)) abort(404);
        return response()->file($filePath);
    }

    public function thumbnail(PdfUpload $pdf)
    {
        $thumbPath = storage_path('app/private/' . $pdf->thumbnail_path);
        if (!file_exists($thumbPath)) abort(404);
        return response()->file($thumbPath);
    }
}
