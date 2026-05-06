<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use setasign\Fpdi\Tcpdf\Fpdi;
use Carbon\Carbon;
use App\Models\Organization;

use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use App\Mail\MemberRegistrationCompleted;
use App\Mail\AgentRegistrationCompleted;
use App\Mail\PreRegisterMail;

use App\Services\FileService;
use App\Services\PdfService;
use App\Enums\Status;

class OrganizationController extends Controller
{
    public function index(Request $request) 
    {
        $user = auth('admin')->user();

        $name = trim((string) $request->input('name'));

        $allowedSorts = 
        [
            'email','created_at', 'fax', 'id', 'name', 'tel'
        ];

        $sortBy  = in_array($request->input('sort_by'), $allowedSorts)
            ? $request->input('sort_by')
            : 'created_at';

        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $per_page = $request->input('per_page') ?? 20;

        $query = Organization::query()->with('member');
        
        if ($name !== '') {
            $keywords = preg_split('/\s+/', $name);

            foreach ($keywords as $word) {
                $query->where(function ($sub) use ($word) {
                    $sub->where('last_name', 'like', "%{$word}%")
                        ->orWhere('first_name', 'like', "%{$word}%");
                });
            }
        }
        
        if ($sortBy === 'name') {
            $query->orderBy('last_name', $sortDir)
                ->orderBy('first_name', $sortDir);
        } elseif ($sortBy === 'address') {
            $query->orderByRaw("
                CONCAT_WS(' ',
                    members.address1,
                    members.address2,
                    members.address3
                ) {$sortDir}
            ");
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $organizations = $query
            ->when($request->filled('organization_date'), fn($q) =>
                $q->whereDate('organization_date', $organizationDate)
            )
            ->when($request->filled('delivery_date'), fn($q) =>
                $q->whereDate('delivery_date', $deliveryDate)
            )
            ->paginate($per_page)
            ->withQueryString();

        return Inertia::render('Admin/Organizations/Index', [
            'user' => $user,
            'organizations' => $organizations,
            'filters' => [
                'name'      => $name,
                'per_page'  => $per_page,
                'sort_by'   => $request->sort_by ?? 'created_at',  // ← 初期値
                'sort_dir'  => $request->sort_dir ?? 'desc',       // ← 初期値
            ],
        ]);

    }


    public function create(Request $request)
    {
        $user = auth('admin')->user();

        return Inertia::render('Admin/Organizations/Edit', [
            'user'        => $user,  
            'organization' => null,
            'filter' => [
                'per_page'      => $per_page,
                'name'          => $name,
                'sort_by'       => $sortBy,
                'sort_dir'      => $sortDir, 
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string',
            'last_name'      => 'required|string',
            'first_name'     => 'required|string',
            'address1'       => 'required|string',
            'address2'       => 'required|string',
            'tel'            => 'required|string',
            'member_id'      => 'required|integer',
        ]);
        // Organization保存
        $organization = Organization::create($data);

        // 成功時リダイレクト
        return redirect()->route('admin.organizations.index')
            ->with('success', '新規登録');
    }


    public function edit(Request $request, Organization $organization)
    {
        $user = auth('admin')->user();

        $organization->load('member');

        $organization->member_name = $organization->member?->name ?? '';

        $name = trim((string) $request->input('name'));

        $allowedSorts = 
        [
            'created_at', 'gender', 'order_code', 'id', 'name', 'age_at_death', 'delivery_date', 'deceased_furigana', 'status'
        ];

        $sortBy  = in_array($request->input('sort_by'), $allowedSorts)
            ? $request->input('sort_by')
            : 'created_at';

        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $per_page = $request->input('per_page') ?? 20;


        return Inertia::render('Admin/Organizations/Edit', [
            'user'        => $user,  
            'organization' => $organization,
            'filter' => [
                'per_page'      => $per_page,
                'name'          => $name,
                'sort_by'       => $sortBy,
                'sort_dir'      => $sortDir, 
            ],
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name'         => 'required|string',
            'abbr'         => 'nullable|string',
            'address1'     => 'required|string',
            'address2'     => 'required|string',
            'address3'     => 'nullable|string',
            'last_name'    => 'nullable|string',
            'first_name'   => 'nullable|string',
            'tel'          => 'required|string',
            'fax'          => 'nullable|string',
            'mobile'       => 'nullable|string',
            'postal_code'  => 'nullable|string',
            'member_id'    => 'required|integer',
            'allow_text_color' => 'required|integer',
            'allow_background_color' => 'required|integer',

        ]);
           // Organization保存
        $organization->update($data);

        // 成功時リダイレクト
        return redirect()->route('admin.organizations.index')
            ->with('success', '更新しました。');
    }

    public function show(Request $request, Organization $organization)
    {

        $user = auth('admin')->user();

        $name = trim((string) $request->input('name'));

        $allowedSorts = 
        [
            'created_at', 'gender', 'order_code', 'id', 'name', 'age_at_death', 'delivery_date', 'deceased_furigana', 'status'
        ];

        $sortBy  = in_array($request->input('sort_by'), $allowedSorts)
            ? $request->input('sort_by')
            : 'created_at';

        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $per_page = $request->input('per_page') ?? 20;


        return Inertia::render('Admin/Organizations/Show', [
            'user'        => $user,  
            'organization' => $organization,
            'filter' => [
                'per_page'      => $per_page,
                'name'          => $name,
                'sort_by'       => $sortBy,
                'sort_dir'      => $sortDir, 
            ],
        ]);
    }



    protected function sendCompletedMails(Member $member, $preUser, $corp, $agent = null): void
    {

        $toUser = filter_var($preUser->email, FILTER_VALIDATE_EMAIL)
            ? $preUser->email
            : null;

        $toCorp = filter_var($corp['email'] ?? null, FILTER_VALIDATE_EMAIL)
            ? $corp['email']
            : null;

        // 本人／代理人宛
        if ($toUser) {
            try {
                $data = $agent ?? $corp; // 代理人がいれば agent、それ以外は member
                Mail::to($toUser)
                    ->send(new MemberRegistrationCompleted($data));

                $member->user_mail_sent_at = now();

            } catch (\Throwable $e) {
                Log::error('SES user mail send failed', [
                    'member_id' => $member->id ?? null,
                    'to' => $toUser,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // corp 宛（agent の場合のみ or mail がある場合）
        if ($toCorp) {
            try {
                Mail::to($toCorp)
                    ->send(new AgentRegistrationCompleted($corp));

                $member->corp_mail_sent_at = now();

            } catch (\Throwable $e) {
                Log::error('SES corp mail send failed', [
                    'member_id' => $member->id ?? null,
                    'to' => $toCorp,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        //メール送信結果をDBへ保存    
        $member->save();
    }

    public function printDocument(Request $request, Organization $organization)
    {
        // ---------------------------
        // 1. OrganizationDocuments の取得
        //    type = 'poem' の PNG のみ
        // ---------------------------
        $documents = $organization->documents()
            ->where('type', 'png') // enumで 'poem' にしている場合
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'file_path' => $doc->file_path,
                    'file_url' => Storage::url($doc->file_path),
                ];
            });

        // ---------------------------
        // 2. 検索条件の受け取り（persistQuery() で渡したもの）
        // ---------------------------
        $filters = $request->only([
            'tenant_id',
            'code',
            'name',
            'status_id',
            'per_page',
            'sort_by',
            'sort_dir',
            'page',
        ]);

        // ---------------------------
        // 3. Inertia に渡す
        // ---------------------------
        return inertia('Organizations/PrintDocument', [
            'organization' => $organization,
            'documents'   => $documents,
            'filters'     => $filters, // Vue 側で検索条件として使える
        ]);
    }

    public function pdfPreview(Request $request,$token)
    {
        return Inertia::render('Members/PdfPreview', [
            'token'  => $token,
            'pdfUrl' => $request->query('pdfUrl'),
        ]);
    }

    public function updateStatus(Request $request, Organization $organization)
    {
        $data = [
            'status' => $request->status,
        ];

        if ($request->status === 'working') {
            $data['working_at'] = $request->date;
        }

        if ($request->status === 'completed') {
            $data['completed_at'] = $request->date;
        }

        $organization->update($data);
        
        return response()->json(['success' => true]);
    }

    public function uploadDocument(Request $request, Organization $organization, FileService $fileService)
    {        
        $request->validate([
            'document' => 'required|file|mimes:png|max:10240',
        ]);

        [$file_path, $thumbnail_path] =
            $fileService->storeUploadedFile(
                $request->file('document'),
                'poem/png'
            );

        $organization->documents()->create([
            'type' => 'png',
            'file_path' => $file_path,
            'thumbnail_path' => $thumbnail_path,
        ]);


        return response()->json([
            'success' => true,
            'file_url' => Storage::url($file_path),
            'thumbnail_url' => $thumbnail_path ? Storage::url($thumbnail_path) : null,
        ]);
    }

}
