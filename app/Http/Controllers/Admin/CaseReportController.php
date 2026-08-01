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
        $sortBy  = $request->input('sort_by', 'submitted_at');
        $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['submitted_at', 'treatment_area', 'patient_gender', 'patient_age_group', 'organization_name'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'submitted_at';

        $query = CaseReport::query()
            ->with(['organization', 'member'])
            ->when($request->organization_id && $request->organization_id !== 'all', fn($q) =>
                $q->where('organization_id', $request->organization_id)
            )
            ->when($request->treatment_area && $request->treatment_area !== 'all', fn($q) =>
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
            );

        if ($sortBy === 'organization_name') {
            // 施設名は organizations との結合ソートが必要なため個別対応
            $query->leftJoin('organizations', 'organizations.id', '=', 'case_reports.organization_id')
                ->orderBy('organizations.name', $sortDir)
                ->select('case_reports.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $reports = $query->paginate((int) $request->input('per_page', 20))->withQueryString();

        $organizations = Organization::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/CaseReports/Index', [
            'reports'       => $reports,
            'organizations' => $organizations,
            'filters'       => $request->only([
                'organization_id', 'treatment_area', 'patient_gender',
                'submitted_from', 'submitted_to', 'per_page', 'sort_by', 'sort_dir',
            ]),
            'treatmentAreas' => ['手', '足', '肘', '肩', '膝'],
        ]);
    }

    // ──────────────────────────────────────────
    // 詳細
    // ──────────────────────────────────────────
    public function show(CaseReport $caseReport)
    {
        $caseReport->load(['organization', 'member', 'detail']);

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