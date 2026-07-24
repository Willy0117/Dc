<?php

namespace App\Http\Controllers;

use App\Models\CaseReport;
use App\Models\CaseHandDetail;
use App\Models\CaseFootDetail;
use App\Models\CaseElbowDetail;
use App\Models\CaseShoulderDetail;
use App\Models\CaseKneeDetail;
use App\Models\FormOption;
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

        $reports = CaseReport::where('organization_id', $organization->id)
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

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
        return Inertia::render('Reports/Create', [
            'options' => FormOption::getForForm(),
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
    // Tier履歴のcase_count更新
    // ──────────────────────────────────────────
    private function updateTierHistory($organization): void
    {
        $history = $organization->currentTierHistory;
        if (!$history) return;

        $count = CaseReport::where('organization_id', $organization->id)
            ->whereBetween('submitted_at', [
                $history->period_start,
                $history->period_end . ' 23:59:59',
            ])
            ->count();

        $history->update(['case_count' => $count]);

        // tier1→2の自動判定
        if ($organization->tier < 2 && $count >= 36) {
            $organization->update(['tier' => 2]);
            $history->update(['tier' => 2]);
        }
    }
}
