<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Imagick;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

use App\Models\Member;
use App\Models\Status;
use App\Models\OrganizationDocument;

use App\Http\Resources\MemberResource;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $query = Member::query()
            ->with([
                'status',
            ])
            ->when(request('status_id'), function ($q, $status_id) {
                $q->where('status_id', $status_id);
            });

        // =====================
        // ソート（membersのみ）
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
        | members 単体で完結するソート
        |--------------------------------------------------------------------------
        */
        if ($sortBy === 'address') {
            $query->orderByRaw("
                CONCAT_WS(' ',
                    members.address1,
                    members.address2,
                    members.address3
                ) {$sortDir}
            ");
        } else {
            $query->orderBy("members.{$sortBy}", $sortDir);
        }
        // =====================
        // ページング + 整形
        // =====================

        $perPage = (int) $request->input('per_page', 20);

        $statuses = Status::select('id', 'name')
            ->orderBy('id')
            ->get();

        $members = $query
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Members/Index', [
            'members' => $members,
            'filters' => [
                'name'         => $request->name ?? '',
                'status_id'    => $request->status_id ?? '',
                'per_page'     => $request->per_page ?? 20,
                'sort_by'      => $request->sort_by ?? 'created_at',  // ← 初期値
                'sort_dir'     => $request->sort_dir ?? 'desc',       // ← 初期値
            ],
            'statuses' => $statuses,
        ]);
    }

    // 作成画面
    public function create()
    {
        return Inertia::render('Admin/Members/Create', [
            'member' => null
        ]);
    }

    // 保存
    public function store(Request $request)
    {
        $validated = $request->validate([



        ]);

        Member::create($validated);

        return redirect()->route('admin.members.index')
            ->with('success', __('member_created'));
    }

    public function show(Request $request, Member $member)
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
        $documents = $member->organizations
            ->flatMap(fn ($org) => $org->documents)
            ->map(fn ($doc) => [
                'type'           => $doc->type,
                'path'           => $doc->file_path ? Storage::url($doc->file_path) : null,
                'thumbnail_path' => $doc->thumbnail_path ? Storage::url($doc->thumbnail_path) : null,
            ]);

        return Inertia::render('Admin/Members/Show', [
            'member' => [
                'id' => $member->id,

                // 申請者
                'first_name' => $member->first_name,
                'last_name'  => $member->last_name,
                'name'       => $member->full_name,

                // ステータス
                'status'   => $member->status,
                'progress' => $member->progress,

                // organization（typeごとに整理）
                'organizations' => $member->organizations->map(fn ($o) => [
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

                'created_at' => $member->created_at,
            ],
            // 検索条件をそのまま渡す
            'filters' => $request->only([
                'name', 'name', 'tel', 'per_page', 'sort_by', 'sort_dir', 'page'
            ]),            
        ]);
    }

    // 編集画面
    public function edit(Member $member, Request $request)
    {
        
        // Inertia に渡す
        return Inertia::render('Admin/Members/Edit', [
            'form' => $member,
            'filters' => $request->only(['name', 'name', 'tel', 'per_page', 'sort_by', 'sort_dir']),
        ]);
    }


    public function update(Request $request, Member $member)
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

        $member->update($data);

        return redirect()
            ->route('admin.member.index', $member)->with('success', '会員情報を更新しました!');
    }

    // 削除
    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('admin.members.index')
            ->with('success', __('member_deleted'));
    }

    // 複数削除
    public function bulkDelete(Request $request)
    {
        Member::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.members.index')
            ->with('success', __('selected_members_deleted'));
    }

    public function autocomplete(Request $request)
    {
        $search = $request->input('q');

        $members = Member::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($search, fn($q) => $q->where('representative', 'like', "%{$search}%"))
            ->orderBy('name', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'label' => "{$m->name} ({$m->representative})"
            ]);

        return response()->json($members);
    }
    // AdminMemberController.php
    public function editStatus(Member $member)
    {
        return response()->json([
            'member' => [
                'status_id' => $member->status_id,
            ],
            'statuses' => Status::select('id', 'name')->orderBy('id')->get(),
        ]);
    }

    public function updateStatus(Request $request, Member $member)
    {
        $request->validate([
            'status_id' => ['required', 'exists:statuses,id'],
        ]);

        $member->update([
            'status_id' => $request->status_id,
        ]);

        return response()->json(['ok' => true]);
    }

    public function editProgress(Member $member)
    {
        return response()->json([
            'member' => [
                'id' => $member->id,
                'progress_id' => $member->progress_id,
            ],
            'progresses' => Progress::select('id', 'name')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function updateProgress(Request $request, Member $member)
    {
        $request->validate([
            'progress_id' => ['required', 'exists:progresses,id'],
        ]);

        $member->update([
            'progress_id' => $request->progress_id,
        ]);

        return response()->json(['ok' => true]);
    }

    public function uploadDocument(Request $request, Member $member)
    {
        $request->validate([
            'type_id' => 'required|integer|in:1,2,3,4',
            'document' => 'required|file|mimes:pdf|max:10240',
        ]);
        // member → organizations（法人）
        $organization = $member->organizations()
            ->where('type', 1)
            ->first();

        if (!$organization) {
            abort(404, '法人organizationが見つかりません');
        }

        $organizationId = $organization->id;

        // type_id によってアップロード先フォルダを振り分け
        $folderMap = [
            1 => 'members/history_certificates',
            2 => 'members/address_certificates',
            3 => 'members/bank_transfer_forms',
            4 => 'members/power_of_attorney',
        ];

        $folder = $folderMap[$request->type_id] ?? 'members/others';

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
