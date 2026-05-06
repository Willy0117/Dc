<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Exam;
use App\Models\ExamDocument;

use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

use App\Services\FileService;

class ExamController extends Controller
{
    public function index()
    {
        return Inertia::render('Exams/Index');
    }

    public function create()
    {
            $member = auth()->user()->member;

            return Inertia::render('Exams/Edit', [
                'member' => $member,
            ]);
    }

    public function store(Request $request, FileService $fileService)
    {
        $user = auth()->user();

        $data = $request->validate([
            'last_name'   => 'required|string',
            'first_name'  => 'required|string',
            'gender'      => 'required|string',
            'birthdate'   => 'required|string',
            'certificate' => 'nullable|file|mimetypes:application/pdf|max:10240',
            'recome1' => 'required_with:certificate|file|mimetypes:application/pdf|max:10240',
            'recome2' => 'required_without:certificate|file|mimetypes:application/pdf|max:10240',
        ]);

        $data['member_id'] = $user->member_id;
        $data['status'] = 'pending';

        // ファイルパス初期化
        $file_path = null;
        $thumbnail_path = null;
        $pdfPath = null;
        $thumbPath = null;

        // DBトランザクション内で処理
        DB::transaction(function () use (
            $request,
            $data,
            $fileService,
            $user,
            &$file_path,
            &$thumbnail_path,
            &$pdfPath,
            &$thumbPath
        ) {

                    // 保存
            $exam = Exam::create($data);

            if ($request->hasFile('certificate')) {
                [$file_path, $thumbnail_path] =
                    $fileService->storeUploadedFile(
                        $request->file('certificate'),
                        'pdf_uploads'
                    );
                ExamDocument::create([
                    'exam_id' => $exam->id,
                    'type' => 'certificate',
                    'file_path' => $file_path,
                    'thumbnail_path' => $thumbnail_path,
                ]);     

            }
            if ($request->hasFile('recome1')) {
                [$file_path, $thumbnail_path] =
                    $fileService->storeUploadedFile(
                        $request->file('recome1'),
                        'pdf_uploads'
                    );
                ExamDocument::create([
                    'exam_id' => $exam->id,
                    'type' => 'recome1',
                    'file_path' => $file_path,
                    'thumbnail_path' => $thumbnail_path,
                ]);    
            }
            if ($request->hasFile('recome2')) {
                [$file_path, $thumbnail_path] =
                    $fileService->storeUploadedFile(
                        $request->file('recome2'),
                        'pdf_uploads'
                    );
                ExamDocument::create([
                    'exam_id' => $exam->id,
                    'type' => 'recome2',
                    'file_path' => $file_path,
                    'thumbnail_path' => $thumbnail_path,
                ]);    
            }
        });

        // 成功時リダイレクト
        return redirect()->route('exams.index')
            ->with('success', '申込を受け付けました。今、しばらくお待ちください。');
    }

    public function reports()
    {



        return Inertia::render('Exams/Reports');
    }

    public function storeReports(Request $request)
    {
        // 保存
    }

    public function show($id)
    {
        // モックデータ
        $application = [
            'id' => $id,
            'name' => '山田 太郎',
            'email' => 'yamada@example.com',
            'birthdate' => '1980-01-01',
            'gender' => '男性',
            'workplace' => '〇〇病院',
            'department' => '腎臓リハビリテーション科',
            'files' => [
                ['label' => 'Certificate', 'name' => 'certificate.pdf'],
                ['label' => 'Recommendation 1', 'name' => 'recommendation_1.pdf'],
                ['label' => 'Recommendation 2', 'name' => 'recommendation_2.pdf'],
            ],
        ];

        // 自験例 1-10
        for ($i = 1; $i <= 10; $i++) {
            $application['files'][] = ['label' => "Case Report $i", 'name' => "self_report_$i.pdf"];
        }

        return Inertia::render('Exams/Show');
    }
}
