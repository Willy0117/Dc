<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElearningAttempt;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ElearningAttemptController extends Controller
{
    // ──────────────────────────────────────────
    // 受験結果一覧（先生単位、全件）
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $attempts = ElearningAttempt::with(['member:id,last_name,first_name', 'organization:id,name'])
            ->whereNotNull('submitted_at')
            ->when($request->organization_id, fn($q, $id) => $q->where('organization_id', $id))
            ->when($request->keyword, function ($q, $kw) {
                $q->where(function ($sub) use ($kw) {
                    $sub->whereHas('member', fn($m) => $m->where('last_name', 'like', "%{$kw}%")->orWhere('first_name', 'like', "%{$kw}%"))
                        ->orWhereHas('organization', fn($o) => $o->where('name', 'like', "%{$kw}%"));
                });
            })
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        $attempts->getCollection()->transform(fn($a) => [
            'id'                => $a->id,
            'member_name'       => $a->member ? "{$a->member->last_name} {$a->member->first_name}" : '-',
            'organization_name' => $a->organization?->name ?? '-',
            'correct_count'     => $a->correct_count,
            'total_questions'   => $a->total_questions,
            'is_passed'         => $a->is_passed,
            'submitted_at'      => $a->submitted_at->format('Y-m-d H:i'),
        ]);

        return Inertia::render('Admin/Elearning/Attempts/Index', [
            'attempts'             => $attempts,
            'organizationOptions'  => Organization::select('id', 'name')->orderBy('name')->get(),
            'filters'              => $request->only(['organization_id', 'keyword']),
        ]);
    }

    // ──────────────────────────────────────────
    // 契約先（病院）単位の受験状況一覧
    // ──────────────────────────────────────────
    public function byOrganization(Request $request, Organization $organization)
    {
        $periodKey = ElearningAttempt::calculatePeriodKey($organization->contract_date);

        $members = $organization->members()
            ->whereHas('user') // ログインアカウントがある先生のみ対象
            ->with(['attempts' => fn($q) => $q->inPeriod($periodKey)->orderByDesc('id')])
            ->get()
            ->map(function ($member) {
                $attempts = $member->attempts;
                $latestAttempt = $attempts->first();

                // 同一先生（doctor_group_id一致）が、期間を問わずどこかで合格していれば合格扱い
                $isPassed = ElearningAttempt::hasPassedByDoctorGroup($member->doctor_group_id, $member->id);

                $passedAttempt = $member->doctor_group_id
                    ? ElearningAttempt::whereHas('member', fn($q) => $q->where('doctor_group_id', $member->doctor_group_id))
                        ->passed()
                        ->orderBy('submitted_at')
                        ->first()
                    : $attempts->firstWhere('is_passed', true);

                return [
                    'member_id'      => $member->id,
                    'member_name'    => "{$member->last_name} {$member->first_name}",
                    'is_passed'      => $isPassed,
                    'attempt_count'  => $attempts->whereNotNull('submitted_at')->count(),
                    'latest_result'  => $latestAttempt && $latestAttempt->submitted_at ? [
                        'correct_count'   => $latestAttempt->correct_count,
                        'total_questions' => $latestAttempt->total_questions,
                        'submitted_at'    => $latestAttempt->submitted_at->format('Y-m-d H:i'),
                    ] : null,
                    'passed_at'      => $passedAttempt?->submitted_at?->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Admin/Elearning/Attempts/ByOrganization', [
            'organization' => $organization->only(['id', 'name']),
            'periodLabel'  => $this->periodLabel($organization->contract_date, $periodKey),
            'members'      => $members,
        ]);
    }

    private function periodLabel($contractDate, string $periodKey): string
    {
        $index = (int) substr($periodKey, strrpos($periodKey, '_') + 1);
        $start = \Carbon\Carbon::parse($contractDate)->addMonths($index * 6);
        $end = $start->copy()->addMonths(6)->subDay();

        return $start->format('Y/m/d') . ' 〜 ' . $end->format('Y/m/d');
    }
}