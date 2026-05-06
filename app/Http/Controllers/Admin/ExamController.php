<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Imagick;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

use App\Models\Exam;
use App\Models\Report;
use App\Models\Status;
use App\Models\OrganizationDocument;

use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $query = Exam::query()
            ->with([
                'member',
            ]);

        // =====================
        // ソート（examsのみ）
        // =====================

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = [
            'id',
            'name',
            'address',
            'tel',
            'email',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        /*
        |--------------------------------------------------------------------------
        | exams 単体で完結するソート
        |--------------------------------------------------------------------------
        */
        if ($sortBy === 'address') {
            $query->orderByRaw("
                CONCAT_WS(' ',
                    exams.address1,
                    exams.address2,
                    exams.address3
                ) {$sortDir}
            ");
        } else {
            $query->orderBy("exams.{$sortBy}", $sortDir);
        }
        // =====================
        // ページング + 整形
        // =====================

        $perPage = (int) $request->input('per_page', 20);

        $exams = $query
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Exams/Index', [
            'exams' => $exams,
            'filters' => [
                'name'         => $request->name ?? '',
                'per_page'     => $request->per_page ?? 20,
                'sort_by'      => $request->sort_by ?? 'created_at',  // ← 初期値
                'sort_dir'     => $request->sort_dir ?? 'desc',       // ← 初期値
            ],
        ]);
    }

    // 作成画面
    public function create()
    {
        return Inertia::render('Admin/Exams/Create', [
            'exam' => null
        ]);
    }

    // 保存
    public function store(Request $request)
    {
        $validated = $request->validate([



        ]);

        Exam::create($validated);

        return redirect()->route('admin.exams.index')
            ->with('success', __('exam_created'));
    }

    public function show(Request $request, Exam $exam)
    {
        // persistQuery() 用に現在のクエリを保持
        $queryParams = $request->only([
            'name',
            'name',
            'tel',
            'per_page',
            'sort_by',
            'sort_dir',
            'page',
        ]);
        // 書類は type ごとに全部取得
        $documents = $exam->organizations
            ->flatMap(fn ($org) => $org->documents)
            ->map(fn ($doc) => [
                'type'           => $doc->type,
                'path'           => $doc->file_path ? Storage::url($doc->file_path) : null,
                'thumbnail_path' => $doc->thumbnail_path ? Storage::url($doc->thumbnail_path) : null,
            ]);

        return Inertia::render('Admin/Exams/Show', [
            'exam' => [
                'id' => $exam->id,

                // 申請者
                'first_name' => $exam->first_name,
                'last_name'  => $exam->last_name,
                'name'       => $exam->full_name,

                // ステータス
                'status'   => $exam->status,
                'progress' => $exam->progress,

                // organization（typeごとに整理）
                'organizations' => $exam->organizations->map(fn ($o) => [
                    'id'           => $o->id,
                    'type'         => $o->type,
                    'name'         => $o->full_name,
                    'postal_code'  => $o->postal_code,
                    'address'      => $o->full_address,
                    'tel'          => $o->tel,
                    'fax'          => $o->fax,
                    'mobile'       => $o->mobile,
                    'email'        => $o->email,
                    'contact_name' => $o->contact_name,
                ]),

                // 書類は独立
                'documents' => $documents,

                'created_at' => $exam->created_at,
            ],
            // 検索条件をそのまま渡す
            'filters' => $request->only([
                'name', 'name', 'tel', 'per_page', 'sort_by', 'sort_dir', 'page'
            ]),            
        ]);
    }

    // 編集画面
    public function edit(Exam $exam, Request $request)
    {
        $exam->load([
            'member.reports',
            'documents',
        ]);

        $reports = $exam->member?->reports ?? collect();

        return Inertia::render('Admin/Exams/Edit', [
            'form' => $exam,
            'reports' => $reports,
            'documents' => $exam->documents ?? [],
            'filters' => $request->only(['name', 'tel', 'per_page', 'sort_by', 'sort_dir']),
        ]);
    }


    public function update(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'postal_code'  => 'nullable|string|regex:/^\d{3}-\d{4}$/',
            'address1'  => 'required|string',
            'address2'  => 'required|string',
            'address3'  => 'nullable|string',
            'position'  => 'nullable|string',
            'first_name'=> 'nullable|string',
            'last_name' => 'nullable|string',
            'tel'    => 'required|string|regex:/^0\d{1,4}-\d{1,4}-\d{3,4}$/',
            'fax'    => 'nullable|string|regex:/^0\d{1,4}-\d{1,4}-\d{3,4}$/',
            'mobile' => 'nullable|string|regex:/^0[5789]0-\d{4}-\d{4}$/',
        ]);

        $exam->update($data);

        return redirect()
            ->route('admin.exam.index', $exam)->with('success', '会員情報を更新しました!');
    }

    // 削除
    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('admin.exams.index')
            ->with('success', __('exam_deleted'));
    }

    // 複数削除
    public function bulkDelete(Request $request)
    {
        Exam::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.exams.index')
            ->with('success', __('selected_exams_deleted'));
    }

    public function autocomplete(Request $request)
    {
        $search = $request->input('q');

        $exams = Exam::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($search, fn($q) => $q->where('representative', 'like', "%{$search}%"))
            ->orderBy('name', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'label' => "{$m->name} ({$m->representative})"
            ]);

        return response()->json($exams);
    }

    public function updateStatus(Request $request, Exam $exam)
    {
        $request->validate([
            'status' => 'required|string',
        ]);
        $exam->update([
            'status' => $request->status,
        ]);

        return response()->json(['ok' => true]);
    }

    public function uploadDocument(Request $request, Exam $exam)
    {
        $request->validate([
            'type_id' => 'required|integer|in:1,2,3,4',
            'document' => 'required|file|mimes:pdf|max:10240',
        ]);
        // exam → organizations（法人）
        $organization = $exam->organizations()
            ->where('type', 1)
            ->first();

        if (!$organization) {
            abort(404, '法人organizationが見つかりません');
        }

        $organizationId = $organization->id;

        // type_id によってアップロード先フォルダを振り分け
        $folderMap = [
            1 => 'exams/history_certificates',
            2 => 'exams/address_certificates',
            3 => 'exams/bank_transfer_forms',
            4 => 'exams/power_of_attorney',
        ];

        $folder = $folderMap[$request->type_id] ?? 'exams/others';

        // PDFアップロード＋サムネイル生成
        [$filePath, $thumbPath] = $this->storePdfWithThumbnail(
            $request->file('document'),
            $folder
        );

        // DB保存（organization_documents）
        OrganizationDocument::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'type' => $request->type_id,
            ],
            [
                'file_path' => $filePath,
                'thumbnail_path' => $thumbPath,
                'verified_at' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'file_url' => Storage::url($filePath),
            'thumbnail_url' => $thumbPath ? Storage::url($thumbPath) : null,
        ]);
    }


    // pdf upload＋thumbnail(png)作成関数    
    private function storePdfWithThumbnail(
        ?UploadedFile $file,
        string $baseDir
    ): array {
/* debug用
logger()->error('BASE DIR DEBUG', [
    'file' => $file,
    'baseDir' => $baseDir,
    'length' => strlen($baseDir),
]);
*/
        if (!$file) {
            return [null, null];
        }

        if (!$file->isValid()) {
            throw new \RuntimeException('Upload is not valid');
        }

        // PDF 保存（public）
        $pdfRelativePath = $file->store($baseDir, 'public');

        if (!$pdfRelativePath) {
            throw new \RuntimeException('PDF store failed');
        }

        $pdfFullPath = storage_path('app/public/' . $pdfRelativePath);

        if (!is_file($pdfFullPath)) {
            throw new \RuntimeException('PDF not found: ' . $pdfFullPath);
        }

        // thumbnail 保存先
        $thumbDir = $baseDir . '/thumbnails';
        if (!Storage::disk('public')->exists($thumbDir)) {
            Storage::disk('public')->makeDirectory($thumbDir);
        }

        $thumbnailRelativePath =
            $thumbDir . '/' . pathinfo($pdfRelativePath, PATHINFO_FILENAME) . '.png';
        $thumbnailFullPath = storage_path('app/public/' . $thumbnailRelativePath);

        // thumbnail 生成
        $imagick = new \Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage($pdfFullPath . '[0]');
        $imagick->setImageFormat('png');
        $imagick->writeImage($thumbnailFullPath);
        $imagick->clear();
        $imagick->destroy();

        return [$pdfRelativePath, $thumbnailRelativePath];
    }    
}
