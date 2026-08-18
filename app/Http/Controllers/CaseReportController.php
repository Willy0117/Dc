<?php

namespace App\Http\Controllers;

use App\Models\CaseReport;
use App\Models\CaseHandDetail;
use App\Models\CaseFootDetail;
use App\Models\CaseElbowDetail;
use App\Models\CaseShoulderDetail;
use App\Models\CaseKneeDetail;
use App\Models\FormOption;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CaseReportController extends Controller
{
    // ──────────────────────────────────────────
    // organization取得（type=1:病院 / type=2:先生）
    // ──────────────────────────────────────────
    private function getOrganization(): ?\App\Models\Organization
    {
        $user = Auth::user();
        return match ((int) $user->type) {
            1 => $user->organization,
            2 => $user->member?->organization,
            default => null,
        };
    }

    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $organization = $this->getOrganization();

        if (!$organization) {
            return Inertia::render('Reports/Index', [
                'reports'      => collect([]),
                'organization' => null,
            ]);
        }

        $reports = CaseReport::with('member')
            ->where('organization_id', $organization->id)
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        $reports->getCollection()->transform(fn($r) => [
            'id'                  => $r->id,
            'submitted_at'        => $r->submitted_at,
            'treatment_area'      => $r->treatment_area,
            'patient_gender'      => $r->patient_gender,
            'patient_age_group'   => $r->patient_age_group,
            'complication_types'  => $r->complication_types,
            'member_name'         => $r->member?->full_name,
        ]);

        return Inertia::render('Reports/Index', [
            'reports'      => $reports,
            'organization' => $organization,
        ]);
    }

    // ──────────────────────────────────────────
    // 作成画面
    // ──────────────────────────────────────────
    public function create()
    {
        $organization = $this->getOrganization();

        $members = $organization
            ? Member::where('organization_id', $organization->id)
                ->orderBy('last_name')
                ->get(['id', 'last_name', 'first_name'])
            : collect([]);

        return Inertia::render('Reports/Create', [
            'options' => FormOption::getForForm(),
            'members' => $members,
        ]);
    }

    // ──────────────────────────────────────────
    // 保存
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $organization = $this->getOrganization();

        if (!$organization) {
            return redirect()->route('reports.index')->withErrors(['error' => '所属施設が見つかりません。']);
        }

        $validated = $request->validate([
            'member_id'          => 'nullable|exists:members,id',
            'patient_gender'     => 'required|in:男性,女性,不明',
            'patient_age_group'  => 'required|string',
            'treatment_area'     => 'required|in:手,足,肘,肩,膝',
            'details'            => 'nullable|array',
            'complication_types' => 'nullable|array',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $details = $validated['details'] ?? [];
        $area    = $validated['treatment_area'];

        $report = CaseReport::create([
            'organization_id'    => $organization->id,
            'member_id'          => $validated['member_id'] ?? null,
            'facility_name_raw'  => $organization->name,
            'patient_gender'     => $validated['patient_gender'],
            'patient_age_group'  => $validated['patient_age_group'],
            'treatment_area'     => $area,
            'complication_types' => $validated['complication_types'] ?? [],
            'notes'              => $validated['notes'] ?? null,
            'submitted_at'       => now(),
        ]);

        // 詳細をJSONで保存
        \App\Models\CaseReportDetail::create([
            'case_report_id' => $report->id,
            'treatment_area' => $area,
            'data'           => $details,
        ]);

        $this->updateTierHistory($organization);

        return redirect()->route('reports.index')
            ->with('success', '症例報告を登録しました。');
    }


    // ──────────────────────────────────────────
    // Tier履歴のcase_count更新・tier自動判定
    // ──────────────────────────────────────────
    private function updateTierHistory($organization): void
    {
        $organization->syncTierFromHistory();
    }
}