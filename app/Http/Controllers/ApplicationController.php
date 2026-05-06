<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use TCPDF_FONTS;
use setasign\Fpdi\Tcpdf\Fpdi;
use Carbon\Carbon;
use App\Models\Application;
use Imagick;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use App\Mail\MemberRegistrationCompleted;
use App\Mail\AgentRegistrationCompleted;
use App\Mail\PreRegisterMail;

use App\Services\FileService;
use App\Services\PdfService;
use App\Enums\Status;

class ApplicationController extends Controller
{
    public function index(Request $request) 
    {
        $user = Auth::user();

        $name = trim((string) $request->input('name'));
        $applicationDate = $request->input('application_date');
        $deliveryDate = $request->input('delivery_date');

        $allowedSorts = 
        [
            'apply_type','created_at', 'gender', 'order_code', 'id', 'name', 'age_at_death', 'delivery_date', 'deceased_furigana', 'status'
        ];

        $sortBy  = in_array($request->input('sort_by'), $allowedSorts)
            ? $request->input('sort_by')
            : 'created_at';

        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $per_page = $request->input('per_page') ?? 20;

        $query = Application::with('pdfDocuments')
                ->where('organization_id', $user->organization_id);

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
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $applications = $query
            ->when($request->filled('application_date'), fn($q) =>
                $q->whereDate('application_date', $applicationDate)
            )
            ->when($request->filled('delivery_date'), fn($q) =>
                $q->whereDate('delivery_date', $deliveryDate)
            )
            ->paginate($per_page)
            ->withQueryString();
            
        return Inertia::render('Applications/Index', [
            'user' => $user,
            'applications' => $applications,
            'statusOptions' => Status::options(),
            'filter' => [
                'per_page'      => $per_page,
                'name'          => $name,
                'delivery_date' => $deliveryDate,
                'application_date' => $applicationDate,
            ],
        ]);

    }


    public function create(Request $request)
    {
        $user = Auth::user();

        $allowTextColor = $user->organization->allow_text_color ?? 0;
        $allowBackgroundColor = $user->organization->allow_background_color ?? 0;

        $now = Carbon::now();

        // 納期計算
        if ($now->hour < 15) {
            $delivery = $now->copy()->addHours(3);
        } else {
            $delivery = $now->copy()->addDay()->setTime(12, 0);
        }

        // 30分丸め
        $minute = $delivery->minute;

        if ($minute > 0 && $minute <= 30) {
            $delivery->setMinute(30);
        } elseif ($minute > 30) {
            $delivery->addHour()->setMinute(0);
        }

        $delivery->setSecond(0);

        $defaultFuneral = Carbon::today()
            ->addDays(2)
            ->setTime(12, 0);

        return Inertia::render('Applications/Create', [
            'defaultFuneralDatetime' => $defaultFuneral->format('Y-m-d\TH:i'),
            'minFuneralDatetime' => Carbon::today()
                ->addDays(2)
                ->setTime(0, 0)
                ->format('Y-m-d\TH:i'),
            'application_date' => $now->format('Y-m-d\TH:i'),
            'delivery_date' => $delivery->format('Y-m-d\TH:i'),
            'user' => [
                'hall_name' => $user->hall_name,
                'tel' => $user->tel,
                'allow_text_color' => $allowTextColor,
                'allow_background_color' => $allowBackgroundColor,

            ],
        ]);
    }

    public function store(Request $request, PdfService $pdfService, FileService $fileService)
    {
        $data = $request->validate([
            'last_name'     => 'required|string',
            'first_name'     => 'required|string',
            'deceased_furigana' => 'required|string',
            'gender'            => 'required|string',
            'chief_mourner_name'=> 'nullable|string',
            'age_at_death'      => 'nullable|integer',
            'relationship_to_deceased' => 'nullable|string',
            'delivery_date'     => 'required|string',
            'funeral_datetime'  => 'required|string',
            'spouse_status'     => 'nullable|string',
            'children_count'    => 'nullable|integer',
            'grandchildren_count'=> 'nullable|integer',
            'staff_name'        => 'required|string',
            'bg_color'          => 'nullable|string',
            'text_color'        => 'nullable|string',
            'traits'            => 'nullable|array',
            'special_notes'     => 'nullable|string',
            'remarks'           => 'nullable|string',
            'canvas'            => 'nullable|string',
        ]);
                   
        $data['apply_type'] = 'web';

        $user = auth()->user();
        $data['organization_id'] = $user->organization_id;

        // ファイルパス初期化
        $file_path = null;
        $thumbnail_path = null;
        $pdfPath = null;
        $thumbPath = null;

        // DBトランザクション内で処理
        DB::transaction(function () use (
            $request,
            $data,
            $pdfService,
            $fileService,
            $user,
            &$file_path,
            &$thumbnail_path,
            &$pdfPath,
            &$thumbPath
        ) {
            // Application保存
            $application = Application::create($data);

            // Canvas画像
            if ($request->filled('canvas')) {
                [$file_path, $thumbnail_path] =
                    $fileService->storeBase64Image($request->canvas, 'poem/canvas');

                $application->documents()->create([
                    'type' => 'canvas',
                    'file_path' => $file_path,
                    'thumbnail_path' => $thumbnail_path,
                ]);
            }

            // PDF生成用ユーザー表示
            $tel = $application->organization->tel ?? '';
            $application->user = $user->name . ' ' . $tel;

            // PDF生成
            $pdfData = $pdfService->createApplicationPdf($application, $file_path ?? null);

            $pdfPath = 'poem/pdf/' . $application->order_code . '.pdf';
            if (!Storage::put($pdfPath, $pdfData)) {
                throw new \Exception('PDF保存失敗');
            }

            $thumbPath = $fileService->createThumbnail($pdfPath, 'poem/pdf');

            $application->documents()->create([
                'type' => 'pdf',
                'file_path' => $pdfPath,
                'thumbnail_path' => $thumbPath,
            ]);
        });

        // 成功時リダイレクト
        return redirect()->route('applications.index')
            ->with('success', '申込を受け付けました。今、しばらくお待ちください。');
    }
/*
    public function store(Request $request, PdfService $pdfService, FileService $fileService)
    {

        $data = $request->validate([
            'last_name'     => 'required|string',
            'first_name'     => 'required|string',
            'deceased_furigana' => 'required|string',
            'gender'            => 'required|string',
            'chief_mourner_name'=> 'nullable|string',
            'age_at_death'      => 'nullable|integer',
            'relationship_to_deceased' => 'nullable|string',
            'delivery_date'     => 'required|string',
            'funeral_datetime'  => 'required|string',
            'spouse_status'     => 'nullable|string',
            'children_count'    => 'nullable|integer',
            'grandchildren_count'=> 'nullable|integer',
            'staff_name'        => 'required|string',
            'bg_color'          => 'nullable|string',
            'text_color'        => 'nullable|string',
            'traits'            => 'nullable|array',
            'special_notes'     => 'nullable|string',
            'remarks'           => 'nullable|string',
            'canvas'            => 'nullable|string|max:5000000',
        ]);

        $data['organization_id'] = auth()->user()->organization_id;

        $pdfPath = null;
        $thumbPath = null;

        try {

            DB::beginTransaction();

            // Application保存
            $application = Application::create($data);

            $file_path = '';

            if ($request->filled('canvas')) {

                [$file_path, $thumbnail_path] =
                    $fileService->storeBase64Image(
                        $request->canvas,
                        'poem/canvas'
                    );

                $application->documents()->create([
                    'type' => 'canvas',
                    'file_path' => $file_path,
                    'thumbnail_path' => $thumbnail_path,
                ]);

            }

            $application->user = Auth::user()->name . ' ' . ($application->organization->tel ?? '');

            // PDF生成
            $pdfData = $pdfService->createApplicationPdf($application, $file_path);

            $pdfPath = 'poem/pdf/' . $application->order_code . '.pdf';

            if (!Storage::put($pdfPath, $pdfData)) {
                throw new \Exception('PDF保存失敗');
            }
            // サムネイル
            $thumbPath = $fileService->createThumbnail($pdfPath, 'poem/pdf');

            // DB登録
            $application->documents()->create([
                'type' => 'pdf',
                'file_path' => $pdfPath,
                'thumbnail_path' => $thumbPath,
            ]);

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            if ($pdfPath && Storage::exists($pdfPath)) {
                Storage::delete($pdfPath);
            }

            if ($thumbPath && Storage::exists($thumbPath)) {
                Storage::delete($thumbPath);
            }

            throw $e;
        }
   


        return redirect()->route('applications.index')
            ->with('success', '申込を受け付けました。今、しばらくお待ちください。');
    }
*/
    public function edit(Request $request)
    {

    }

    public function show(Request $request, Application $application)
    {
        $application->load([
            'canvasDocument'
        ]);

        $user = Auth::user();

        $name = trim((string) $request->input('name'));
        $applicationDate = $request->input('application_date');
        $deliveryDate = $request->input('delivery_date');

        $allowedSorts = 
        [
            'created_at', 'gender', 'order_code', 'id', 'name', 'age_at_death', 'delivery_date', 'deceased_furigana', 'status'
        ];

        $sortBy  = in_array($request->input('sort_by'), $allowedSorts)
            ? $request->input('sort_by')
            : 'created_at';

        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $per_page = $request->input('per_page') ?? 20;


        return Inertia::render('Applications/Show', [
            'user'        => $user,  
            'application' => $application,
            'filter' => [
                'per_page'      => $per_page,
                'name'          => $name,
                'delivery_date' => $deliveryDate,
                'application_date' => $applicationDate,
                'sort_by'       => $sortBy,
                'sort_dir'      => $sortDir, 
            ],
        ]);
    }


    public function fax(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        // 納期計算
        if ($now->hour < 15) {
            $delivery = $now->copy()->addHours(3);
        } else {
            $delivery = $now->copy()->addDay()->setTime(12, 0);
        }

        // 30分丸め
        $minute = $delivery->minute;

        if ($minute > 0 && $minute <= 30) {
            $delivery->setMinute(30);
        } elseif ($minute > 30) {
            $delivery->addHour()->setMinute(0);
        }

        $delivery->setSecond(0);

        $defaultFuneral = Carbon::today()
            ->addDays(2)
            ->setTime(12, 0);

        return Inertia::render('Applications/Fax', [
            'defaultFuneralDatetime' => $defaultFuneral->format('Y-m-d\TH:i'),
            'minFuneralDatetime' => Carbon::today()
                ->addDays(2)
                ->setTime(0, 0)
                ->format('Y-m-d\TH:i'),
            'application_date' => $now->format('Y-m-d\TH:i'),
            'delivery_date' => $delivery->format('Y-m-d\TH:i'),
            'user' => [
                'hall_name' => $user->hall_name,
                'tel' => $user->tel,
            ],
        ]);
    }

    public function faxstore(Request $request, PdfService $pdfService, FileService $fileService)
    {
        DB::transaction(function () use ($request, $fileService) {

            $data = $request->validate([
                'delivery_date' => 'required|string',
                'last_name' => 'required|string',
                'first_name' => 'required|string',
                'deceased_furigana' => 'required|string',
                'staff_name' => 'required|string',
                'organization_id' => 'required|integer',
                'pdf' => 'required|file|mimes:pdf|max:10240',
                'remarks'           => 'nullable|string',
                'gender'            => 'required|string',
            ]);

            $data['apply_type'] = 'fax';

            unset($data['pdf']);

            $application = Application::create($data);

            if ($request->hasFile('pdf')) {

                [$file_path, $thumbnail_path] =
                    $fileService->storeUploadedFile(
                        $request->file('pdf'),
                        'poem/pdf'
                    );

                $application->documents()->create([
                    'type' => 'pdf',
                    'file_path' => $file_path,
                    'thumbnail_path' => $thumbnail_path,
                ]);
            }

        });
   


        return redirect()->route('applications.index')
            ->with('success', '申込を受け付けました。今、しばらくお待ちください。');
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

    public function printDocument(Request $request, Application $application)
    {
        // ---------------------------
        // 1. ApplicationDocuments の取得
        //    type = 'poem' の PNG のみ
        // ---------------------------
        $documents = $application->documents()
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
            'delivary_date',
            'application_date',
            'gender',
            'status_id',
            'per_page',
            'sort_by',
            'sort_dir',
            'page',
        ]);

        // ---------------------------
        // 3. Inertia に渡す
        // ---------------------------
        return inertia('Applications/PrintDocument', [
            'application' => $application,
            'documents'   => $documents,
            'filters'     => $filters, // Vue 側で検索条件として使える
        ]);
    }
    // Apuls Pdf Generate
    public function pdfGenerate(Request $request)
    {
        $data = $request->validate([
            'last_name'     => 'nullable|string',
            'first_name'     => 'nullable|string',
            'deceased_furigana' => 'nullable|string',
            'gender'            => 'nullable|string',
            'chief_mourner_name'=> 'nullable|string',
            'age_at_death'      => 'nullable|integer',
            'relationship_to_deceased' => 'nullable|string',
            'delivery_date'     => 'nullable|string',
            'funeral_datetime'  => 'nullable|string',
            'spouse_status'     => 'nullable|string',
            'children_count'    => 'nullable|integer',
            'grandchildren_count'=> 'nullable|integer',
            'staff_name'        => 'nullable|string',
            'bg_color'          => 'nullable|string',
            'text_color'        => 'nullable|string',
            'traits'            => 'nullable|array',
            'special_note'      => 'nullable|string',
            'remarks'           => 'nullable|string',
            'canvas'            => 'nullable|string',
        ]);
              // FPDI + TCPDF
        $pdf = new Fpdi();
        // ヘッダーフッター消し
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // ページ追加
        $pdf->AddPage();

        // 既存PDFテンプレート読み込み
        $templatePath = storage_path('app/templates/order_sheet/poem.pdf');
        $pageCount = $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        // TCPDF同梱の日本語フォント
        $pdf->SetFont('kozminproregular', '', 11); // もしくは cid0jp
        // 受注コード
        $pdf->SetXY(158, 18);
        $pdf->Write(8, 'P12345678'); // $application->order_code
        //納品時間    
        $pdf->SetXY(50, 48);
        $pdf->Write(8, ($data['delivery_date']??now())->format('Y年m月d日 H時i分'));
        //申込時間    
        $pdf->SetXY(135, 48);
        $pdf->Write(8, now()->format('Y年m月d日 H時i分'));

        $pdf->SetXY(65, 62);
        $pdf->Write(8, $data['staff_name']??'山田　太郎');
        $pdf->SetXY(65, 69);
        $pdf->Write(8, $data['user_name']??'那珂ホール');
        $pdf->SetXY(65, 76);
        $pdf->Write(8, ($data['funeral_datetime']??now())->format('Y年m月d日 H時i分'));
        $pdf->SetXY(65, 83);
        $pdf->Write(8, '名前の詩');
        $pdf->SetXY(65, 90);
        $pdf->Write(8, $data['deceased_furigana']??'やまだ　たろう');
        $pdf->SetFont('kozminproregular', '', 14); // もしくは cid0jp

        $name = ($data['last_name']??'山田') . ' ' . ($data['first_name']??'太郎');
        $pdf->SetXY(65,  99);
        $pdf->Write(8, $name);
        $pdf->SetFont('kozminproregular', '', 11); // もしくは cid0jp

        $pdf->SetXY(65, 107);
        $pdf->Write(8, $data['gender']??'男');
        $pdf->SetXY(65, 114);
        $pdf->Write(8, ($data['age_at_death']??'90') . '　歳');
        $pdf->SetXY(65, 121);
        $pdf->Write(8, $data['spouse_status']??'有');
        $pdf->SetXY(65, 128);
        $pdf->Write(8, $data['children_count']??'未記入');
        $pdf->SetXY(65, 135);
        $pdf->Write(8, $data['grandchildren_count']??'未記入');
        $pdf->SetXY(65, 142);
        $pdf->Write(8, $data['chief_mourner_name']??'未記入');
        $pdf->SetXY(65, 149);
        $pdf->Write(8, $data['relationship_to_deceased']??'未記入');

        $text_color = $data['text_color']??'brown';
        $colorLabels = [
            'brown'  => '茶',
            'green'  => '緑',
            'pink'   => 'ピンク',
            'blue'   => '青',
            'orange' => 'オレンジ',
            'yellow' => '黄色',
        ];
        $pdf->SetXY(65, 155);
        $pdf->Write(8, $colorLabels[$text_color]);

        $bg_color = $data['bg_color']??'none';
        $colorLabels = [
            'none'   => 'なし',
            'green'  => '緑',
            'pink'   => 'ピンク',
            'blue'   => '青',
            'orange' => 'オレンジ',
        ];
        $pdf->SetXY(65, 162);
        $pdf->Write(8, $colorLabels[$bg_color]);

        $traitsOptions = [
            '優しい','明朗','温和','誠実','思いやり','面倒見良い','忍耐強い',
            '親切','真面目','努力家','積極的','責任感が強い','世話好き'
        ];

        $traits = $data['traits'] ?? [];

        if (is_string($traits)) {
            $traits = json_decode($traits, true) ?? [];
        }

        if (is_string($traits)) {
            $traits = json_decode($traits, true) ?? [];
        }
        $traitsText = '';

        foreach ($traitsOptions as $trait) {
            $traitsText .= in_array($trait, $traits) ? '■'.$trait.'  ' : '□'.$trait.'  ';
        }
        $pdf->SetXY(65, 171);
        $pdf->MultiCell(120,7,$traitsText);

        $pdf->SetXY(65, 197);
        $pdf->MultiCell(120, 8, $data['special_note'] ?? 'なし');

        $pdf->SetXY(65, 224);
        $pdf->MultiCell(120, 8, $data['remarks'] ?? 'なし');
        // Canvasがある場合は貼り付け
        //$canvas = $application->documents()
        //    ->where('type', 'canvas')
        //    ->first();
        $canvas = new \stdClass();
        $canvas->file_path = 'public/png/pink_thumb.png';
        if ($canvas) {
            $canvasPath = storage_path('app/' . $canvas->file_path);
            if (file_exists($canvasPath)) {
                // x, y, width などは調整
                $pdf->Image($canvasPath, 150, 108, 32);
            }
        }
        
        return response($pdf->Output('','I'))
            ->header('Content-Type', 'application/pdf');

    }

    public function pdfPreview(Request $request,$token)
    {
        return Inertia::render('Members/PdfPreview', [
            'token'  => $token,
            'pdfUrl' => $request->query('pdfUrl'),
        ]);
    }

    public function updateStatus(Request $request, Application $application)
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

        $application->update($data);
        
        return response()->json(['success' => true]);
    }

    public function uploadDocument(Request $request, Application $application, FileService $fileService)
    {
        \Log::info($request);

        $request->validate([
            'document' => 'required|file|mimes:png|max:10240',
        ]);


        [$file_path, $thumbnail_path] =
            $fileService->storeUploadedFile(
                $request->file('document'),
                'poem/png'
            );

        $application->documents()->create([
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
