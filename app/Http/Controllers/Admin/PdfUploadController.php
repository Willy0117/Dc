<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdfUpload;
use Illuminate\Http\Request;

class PdfUploadController extends Controller
{
    // PDFアップロード一覧
    public function index()
    {
        // member, creditCategory, creditConference, creditRole をまとめてロード
        $uploads = PdfUpload::with([
            'member:id,name',
            'creditCategory:id,name',
            'creditConference:id,name',
            'creditRole:id,role'
        ])->latest()->get();

        return inertia('Admin/PdfUploads/Index', [
            'uploads' => $uploads,
        ]);
    }
   // 承認
    public function approve(PdfUpload $pdf)
    {
        $pdf->status = 'approved';
        $pdf->save();

        return back()->with('success', __('PDF approved successfully.'));
    }

    // 差し戻し
    public function reject(Request $request, PdfUpload $pdf)
    {
        $request->validate([
            'rejection_message' => 'required|string|max:500',
        ]);

        $pdf->status = 'rejected';
        $pdf->rejection_message = $request->rejection_message;
        $pdf->points = 0; // 差し戻しは単位なし
        $pdf->save();

        return back()->with('success', __('PDF rejected.'));
    }

    // PDF閲覧（管理者もprivateフォルダ参照）
    public function view(PdfUpload $pdf)
    {
        $filePath = storage_path('app/private/' . $pdf->file_path);
        if (!file_exists($filePath)) abort(404);

        return response()->file($filePath);
    }

    // サムネイル取得
    public function thumbnail(PdfUpload $pdf)
    {
        $thumbPath = storage_path('app/private/' . $pdf->thumbnail_path);
        if (!file_exists($thumbPath)) abort(404);

        return response()->file($thumbPath);
    }
}


