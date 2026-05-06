<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;

use Imagick;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

use App\Services\FileService;
use App\Services\PdfService;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::query()
            ->where('member_id', auth()->user()->member_id);
        $perPage = 10;
        $reports = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Reports/Index', [
           'reports' =>$reports, 
        ]);
    }

    public function create()
    {
            $member = auth()->user()->member;

            return Inertia::render('Reports/Edit', [
                'member' => $member,
                'report' => '',
            ]);
    }

    public function store(Request $request, PdfService $pdfService, FileService $fileService)
    {
        $user = auth()->user();

        $data = $request->validate([
            'is_detailed' => 'required|boolean',
            'facility_name' => 'required|string',
            'age'           => 'required|integer',
            'gender'        => 'required|string',
            'visit_type'    => 'required|string',
            'diagnosis'     => 'required|string',
            'current_history'=> 'required|string',
            'past_history'  => 'required|string',
            'rehab_program' => 'required|string',
            'future_plan'   => 'required|string',
            'supervisor'    => 'required_if:is_detailed,1',
            'body_build' => 'required_if:is_detailed,1',
            'findings_assessment' => 'required_if:is_detailed,1',
        ]);

        $data['member_id'] = $user->member_id;
        // 保存
        $report = Report::create($data);

        // 成功時リダイレクト
        return redirect()->route('reports.index')
            ->with('success', '自験例報告を登録をしました。');
    }

    public function edit(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        return Inertia::render('Reports/Edit', [
            'report' => $report,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'is_detailed'   => 'required|boolean',
            'facility_name' => 'required|string',
            'age'           => 'required|integer',
            'gender'        => 'required|string',
            'visit_type'    => 'required|string',
            'diagnosis'     => 'required|string',
            'current_history'=> 'required|string',
            'past_history'  => 'required|string',
            'rehab_program' => 'required|string',
            'future_plan'   => 'required|string',
            'supervisor'    => 'required_if:is_detailed,1',
            'body_build'    => 'required_if:is_detailed,1',
            'findings_assessment' => 'required_if:is_detailed,1',
        ]);

        $report = Report::where('id', $id)
            ->where('member_id', auth()->user()->member_id)
            ->firstOrFail();

        $report->update($data);

        return redirect()->route('reports.index')
            ->with('success', '更新しました');
    }

}
