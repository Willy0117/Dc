<?php

namespace App\Http\Controllers;

use App\Models\RehabApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RehabApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ログインユーザーの申込データを取得
        $applications = RehabApplication::where('user_id', $user->id)->get();

        return Inertia::render('Member/RehabApplicationsTable', [
            'applications' => $applications,
        ]);
    }

    // 自己申告フォーム表示
    public function create()
    {
        $application = auth()->user()->rehabApplication ?? null;

        return Inertia::render('RehabApply/Form', [
            'application' => $application
        ]);
    }

    // 自己申告フォーム保存
    public function store(Request $request)
    {
        $data = $request->validate([
            'facility' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|in:male,female,other',
            'visit_type' => 'required|in:outpatient,inpatient',
            'diagnosis' => 'required|string',
            'current_history' => 'required|string',
            'past_history' => 'nullable|string',
            'rehab' => 'nullable|string',
            'future_plan' => 'nullable|string',
        ]);

        $application = auth()->user()->rehabApplication()->updateOrCreate([], $data);

        return redirect()->route('rehab.files.edit')->with('success', '自己申告を保存しました');
    }

    // PDFアップロード画面表示
    public function editFiles()
    {
        $application = auth()->user()->rehabApplication;

        if (!$application) {
            return redirect()->route('rehab.create')->with('error', '先に申込フォームを入力してください');
        }

        return Inertia::render('RehabApply/FileUpload', [
            'application' => $application
        ]);
    }

    // PDF個別アップロード
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_type' => 'required|in:recommendation_pdf,clinical_report_1,clinical_report_2',
            'file' => 'required|file|mimes:pdf|max:10240'
        ]);

        $application = auth()->user()->rehabApplication;

        $file = $request->file('file');
        $path = $file->store('rehab_pdfs', 'public');

        $application->{$request->pdf_type} = $path;
        $application->save();

        return back()->with('success', 'PDFをアップロードしました');
    }

    // 管理者用：申込差し戻し
    public function reject(RehabApplication $application, Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $application->status = 'rejected';
        $application->reject_message = $request->message;
        $application->save();

        return back()->with('success', '申込を差し戻しました');
    }

    // 管理者用：申込承認
    public function approve(RehabApplication $application)
    {
        $application->status = 'approved';
        $application->save();

        return back()->with('success', '申込を承認しました');
    }
    // ユーザー自身の申込一覧を返す
    public function userApplications(Request $request)
    {
        $user = $request->user();

        $applications = RehabApplication::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($applications);
    }    
}
