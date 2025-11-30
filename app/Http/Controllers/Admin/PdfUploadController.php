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
        $uploads = PdfUpload::with('member')->latest()->get();

        return inertia('Admin/PdfUploads/Index', [
            'uploads' => $uploads,
        ]);
    }
   // 承認
    public function approve(PdfUpload $pdf)
    {
        $pdf->status = 'approved';

        // 承認時に単位を反映（仮に1単位）
        $pdf->unit = $this->calculateUnit($pdf);
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
        $pdf->unit = 0; // 差し戻しは単位なし
        $pdf->save();

        return back()->with('success', __('PDF rejected.'));
    }

    /**
     * 単位計算（例: カテゴリごとに単位を決める）
     */
    protected function calculateUnit(PdfUpload $pdf)
    {
        switch($pdf->category) {
            case 'conference':
                return 2;
            case 'seminar':
                return 1;
            case 'journal':
                return 3;
            default:
                return 0;
        }
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


