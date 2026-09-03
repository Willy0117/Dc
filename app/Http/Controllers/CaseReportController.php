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
    // member取得（type=2:先生ログイン時のみ本人が取れる。type=1:病院ログインはnull）
    // 変更点9：症例報告は先生が主体のため、先生ログイン時は自分自身を特定する
    // ──────────────────────────────────────────
    private function getLoggedInMember(): ?Member
    {
        $user = Auth::user();
        return (int) $user->type === 2 ? $user->member : null;
    }

    // ──────────────────────────────────────────
    // 一覧
    // 変更点2：病院単位ではなく、
    //   ・先生ログイン時 → 自分自身が担当した症例のみ
    //   ・病院ログイン時 → 所属する全ての先生の症例（従来通り、施設単位で俯瞰）
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $organization = $this->getOrganization();
        $loggedInMember = $this->getLoggedInMember();

        if (!$organization) {
            return Inertia::render('Reports/Index', [
                'reports'      => collect([]),
                'organization' => null,
            ]);
        }

        $query = CaseReport::with('member')
            ->orderByDesc('submitted_at');

        if ($loggedInMember) {
            // 先生ログイン：自分自身のmember_idで絞り込む
            $query->where('member_id', $loggedInMember->id);
        } else {
            // 病院ログイン：所属organizationの全先生分
            $query->where('organization_id', $organization->id);
        }

        $reports = $query->paginate(20)->withQueryString();

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
        $organization   = $this->getOrganization();
        $loggedInMember = $this->getLoggedInMember();

        $members = $organization
            ? Member::where('organization_id', $organization->id)
                ->orderBy('last_name')
                ->get(['id', 'last_name', 'first_name'])
            : collect([]);

        // 変更点③：治療部位（カテゴリー）を動的に取得。
        // 先生ログインの場合は、自分のグレード以上のカテゴリーのみに絞り込む。
        // 病院ログインの場合は、常にベーシック(1)扱いで絞り込む
        // （①の資料一覧と同じ考え方：グレードは先生個人の実績のため）。
        $effectiveTier = $loggedInMember?->tier ?? 1;

        $categories = \App\Models\CaseReportCategory::active()
            ->availableForTier($effectiveTier)
            ->orderBy('sort_order')
            ->pluck('name');

        return Inertia::render('Reports/Create', [
            'options'         => FormOption::getForForm(),
            'members'         => $members,
            'treatmentAreas'  => $categories, // 追加：グレードで絞り込み済みの部位一覧
        ]);
    }

    // ──────────────────────────────────────────
    // 保存
    // 変更点9・③：
    //   ・先生ログイン時 → member_idはリクエストの値を信用せず、
    //                       サーバー側で強制的に自分自身にする（なりすまし防止）
    //   ・病院ログイン時 → member_idは必須（未選択ならエラー）
    // ──────────────────────────────────────────
    public function store(Request $request)
    {
        $organization   = $this->getOrganization();
        $loggedInMember = $this->getLoggedInMember();

        if (!$organization) {
            return redirect()->route('reports.index')->withErrors(['error' => '所属施設が見つかりません。']);
        }

        // 変更点③：治療部位（カテゴリー）は動的なので、ハードコードのinルールではなく、
        // 現在有効なカテゴリー名の一覧から検証する
        $validCategoryNames = \App\Models\CaseReportCategory::active()->pluck('name')->toArray();

        $validated = $request->validate([
            // 病院ログイン時は必須、先生ログイン時はこの値自体を使わないためnullableのままでよい
            'member_id'          => [$loggedInMember ? 'nullable' : 'required', 'exists:members,id'],
            'patient_gender'     => 'required|in:男性,女性,不明',
            'patient_age_group'  => 'required|string',
            'treatment_area'     => ['required', 'string', 'in:' . implode(',', $validCategoryNames)],
            'details'            => 'nullable|array',
            'complication_types' => 'nullable|array',
            'notes'              => 'nullable|string|max:1000',
        ]);

        // 変更点③：先生ログインの場合、自分のグレード未満のカテゴリーを
        // 直接POSTで送りつけてくる不正な操作を防ぐ（フロントのUI制限だけに頼らない）
        if ($loggedInMember) {
            $category = \App\Models\CaseReportCategory::where('name', $validated['treatment_area'])->first();
            if ($category && $category->required_tier > $loggedInMember->tier) {
                return back()->withErrors([
                    'treatment_area' => 'このカテゴリーは、あなたの現在のグレードでは選択できません。',
                ]);
            }
        }

        // 先生ログインなら常に自分自身のmember_idを使う（リクエスト値は無視）
        // 病院ログインならバリデーション済みの選択値をそのまま使う
        $memberId = $loggedInMember ? $loggedInMember->id : $validated['member_id'];

        $details = $validated['details'] ?? [];
        $area    = $validated['treatment_area'];

        $report = CaseReport::create([
            'organization_id'    => $organization->id, // 変更点9：補足情報として残す
            'member_id'          => $memberId,
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

        $this->updateTierHistory($report);

        return redirect()->route('reports.index')
            ->with('success', '症例報告を登録しました。');
    }


    // ──────────────────────────────────────────
    // Tier履歴のcase_count更新・tier自動判定
    // 変更点1：Organization起点ではなく、症例に紐づくMember起点で行う
    // ──────────────────────────────────────────
    private function updateTierHistory(CaseReport $report): void
    {
        $report->member?->syncTierFromHistory();
    }
}