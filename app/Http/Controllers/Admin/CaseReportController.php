<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseReport;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaseReportController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $reports = CaseReport::query()
            ->with(['organization'])
            ->when($request->organization_id, fn($q) =>
                $q->where('organization_id', $request->organization_id)
            )
            ->when($request->treatment_area, fn($q) =>
                $q->where('treatment_area', $request->treatment_area)
            )
            ->when($request->patient_gender, fn($q) =>
                $q->where('patient_gender', $request->patient_gender)
            )
            ->when($request->submitted_from, fn($q) =>
                $q->where('submitted_at', '>=', $request->submitted_from)
            )
            ->when($request->submitted_to, fn($q) =>
                $q->where('submitted_at', '<=', $request->submitted_to . ' 23:59:59')
            )
            ->orderByDesc('submitted_at')
            ->paginate((int) $request->input('per_page', 20))
            ->withQueryString();

        $organizations = Organization::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/CaseReports/Index', [
            'reports'       => $reports,
            'organizations' => $organizations,
            'filters'       => $request->only([
                'organization_id', 'treatment_area', 'patient_gender',
                'submitted_from', 'submitted_to', 'per_page',
            ]),
            'treatmentAreas' => ['手', '足', '肘', '肩', '膝'],
        ]);
    }

    // ──────────────────────────────────────────
    // 詳細
    // ──────────────────────────────────────────
    public function show(CaseReport $caseReport)
    {
        $caseReport->load(['organization', 'detail']);

        return Inertia::render('Admin/CaseReports/Show', [
            'report' => $caseReport,
        ]);
    }

    // ──────────────────────────────────────────
    // 削除
    // ──────────────────────────────────────────
    public function destroy(CaseReport $caseReport)
    {
        $caseReport->delete();

        return redirect()->route('admin.case-reports.index')
            ->with('success', '症例報告を削除しました。');
    }
}
