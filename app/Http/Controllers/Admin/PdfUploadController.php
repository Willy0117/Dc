<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdfUpload;
use App\Models\CreditCategory;
use App\Models\CreditConference;
use App\Models\CreditRolePoint;

use Illuminate\Http\Request;

class PdfUploadController extends Controller
{
    // PDFアップロード一覧
    public function index(Request $request)
    {
        // member, creditCategory, creditConference, creditRole をまとめてロード
        $query = PdfUpload::with([
            'member:id,name',
            'creditCategory:id,name',
            'creditConference:id,name',
            'creditRole:id,role'
        ]);

        $query->when($request->filled('category_id'), fn($q) =>
            $q->where('credit_category_id', $request->category_id)
        )->when($request->filled('conference_id'), fn($q) =>
            $q->where('credit_conference_id', $request->conference_id)
        )->when($request->filled('role_id'), fn($q) =>
            $q->where('credit_role_id', $request->role_id)
        );

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = [
            'name',
            'status',
            'created_at',
            'credit_category_id',
            'credit_conference_id',
            'credit_role_id',
            'session',
        ];

        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        /*
        |--------------------------------------------------------------------------
        | members 単体で完結するソート
        |--------------------------------------------------------------------------
        */
        $query->orderBy($sortBy, $sortDir);

        // =====================
        // ページング + 整形
        // =====================

        $perPage = (int) $request->input('per_page', 20);

        $uploads = $query
            ->paginate($perPage)
            ->withQueryString();

        $categories = CreditCategory::all();
        $conferences = CreditConference::all();

        $roles = CreditRolePoint::all()->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->role,
            'points' => $r->points,
            'credit_category_id' => $r->credit_category_id,
            'credit_conference_id' => $r->credit_conference_id,
        ]);

        return inertia('Admin/PdfUploads/Index', [
            'uploads' => $uploads,
            'categories' => $categories,
            'conferences' => $conferences,
            'roles' => $roles,
            'filters' => 
            [
                'name'      => $request->name ?? '',
                'status'    => $request->status ?? '',
                'category_id'    => $request-> category_id ?? '',
                'conference_id'  => $request-> conference_id ?? '',
                'role_id'    => $request->role_id ?? '',
                'per_page'  => $request->per_page ?? 20,
                'sort_by'   => $request->sort_by ?? 'created_at',  // ← 初期値
                'sort_dir'  => $request->sort_dir ?? 'desc',       // ← 初期値
            ],
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


