<?php

namespace App\Http\Controllers;

use App\Models\PdfUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Imagick;

class PdfUploadController extends Controller
{
    // 会員のアップロード一覧
    public function index()
    {
        $uploads = PdfUpload::where('member_id', Auth::id())
            ->latest()
            ->get();

        return inertia('PdfUploads/Index', [
            'uploads' => $uploads
        ]);
    }
    /**
     * 会員のPDFアップロード
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
            'category' => 'required|in:conference,seminar,journal',
            'role' => 'required|string|max:255',
            'organization_name' => 'required|string|max:255',
        ]);

        // private/pdf_uploads に保存
        if (!Storage::disk('private')->exists('pdf_uploads')) {
            Storage::disk('private')->makeDirectory('pdf_uploads');
        }
        $path = $request->file('file')->store('pdf_uploads', 'private');

        // DB作成
        $upload = PdfUpload::create([
            'member_id' => Auth::id(),
            'file_path' => $path,
            'category' => $request->category,
            'role' => $request->role,
            'organization_name' => $request->organization_name,
            'status' => 'pending',
            'unit' => 0,
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
        //$this->authorize('view', $pdf);

        $filePath = storage_path('app/private/' . $pdf->file_path);
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }

    public function thumbnail(PdfUpload $pdf)
    {
        //$this->authorize('view', $pdf);

        $thumbPath = storage_path('app/private/' . $pdf->thumbnail_path);
        if (!file_exists($thumbPath)) {
            abort(404);
        }

        return response()->file($thumbPath);
    }
}


