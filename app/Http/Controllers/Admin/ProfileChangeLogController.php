<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Organization;
use App\Models\ProfileChangeLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileChangeLogController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $logs = ProfileChangeLog::with('user:id,name,type,organization_id,member_id')
            ->when($request->target_type && $request->target_type !== 'all',
                fn($q, $t) => $q->where('target_type', $request->target_type)
            )
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $logs->getCollection()->transform(function ($log) {
            // 対象名（病院名 or 先生名）を解決
            $targetName = null;
            if ($log->target_type === ProfileChangeLog::TARGET_ORGANIZATION) {
                $targetName = Organization::find($log->target_id)?->name;
            } elseif ($log->target_type === ProfileChangeLog::TARGET_MEMBER) {
                $member = Member::find($log->target_id);
                $targetName = $member ? "{$member->last_name} {$member->first_name}" : null;
            }

            return [
                'id'           => $log->id,
                'user_name'    => $log->user?->name ?? '-',
                'target_type'  => $log->target_type,
                'target_label' => $log->target_label,
                'target_name'  => $targetName,
                'changes'      => $log->changes,
                'created_at'   => $log->created_at->format('Y-m-d H:i'),
            ];
        });

        return Inertia::render('Admin/ProfileChangeLogs/Index', [
            'logs'         => $logs,
            'targetLabels' => ProfileChangeLog::TARGET_LABELS,
            'filters'      => $request->only(['target_type']),
        ]);
    }
}
