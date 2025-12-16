<?php
namespace App\Http\Controllers;

use App\Models\Temperature;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Process;
use App\Models\Menu;

class TemperatureController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Temperature::query();

        // 検索
        if ($menuId = $request->input('menu_id')) {
            $query->where('menu_id', $menuId);
        }
        if ($sensorId = $request->input('sensor_id')) {
            $query->where('sensor_id', $sensorId);
        }
        if ($deviceId = $request->input('device_id')) {
            $query->where('device_id', $deviceId);
        }
        if ($operatorId = $request->input('operator_id')) {
            $query->where('operator_id', $operatorId);
        }

        if ($handyNo = $request->input('handy_no')) {
            $query->where('handy_no', $handyNo);
        }

        // 日付絞り込み（献立日 or 調理日）
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $dateType = $request->input('date_type', 'serving'); // デフォルト献立日

        if ($dateFrom) {
            if ($dateType === 'serving') {
                $query->whereHas('menu', fn($q) => $q->where('serving_date', '>=', $dateFrom));
            } else {
                $query->whereHas('menu', fn($q) => $q->where('cooking_date', '>=', $dateFrom));
            }
        }
        if ($dateTo) {
            if ($dateType === 'serving') {
                $query->whereHas('menu', fn($q) => $q->where('serving_date', '<=', $dateTo));
            } else {
                $query->whereHas('menu', fn($q) => $q->where('cooking_date', '<=', $dateTo));
            }
        }

        $sort = $request->query('sort', 'updated_at');
        $dir  = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        if ($sort === 'menu_date') {

            $dateType = $request->query('date_type', 'serving');

            if ($dateType === 'cooking') {
                // 調理日 → 献立日 → 配膳時刻
                $query->orderBy(
                    Menu::select('cooking_date')
                        ->whereColumn('menus.id', 'temperature_logs.menu_id'),
                    $dir
                )->orderBy(
                    Menu::select('serving_date')
                        ->whereColumn('menus.id', 'temperature_logs.menu_id'),
                    'asc'
                );
            } else {
                // 献立日 → 配膳時刻
                $query->orderBy(
                    Menu::select('serving_date')
                        ->whereColumn('menus.id', 'temperature_logs.menu_id'),
                    $dir
                );
            }

            // 共通：配膳時刻 → ログの時系列
            $query->orderBy(
                Menu::select('serving_time')
                    ->whereColumn('menus.id', 'temperature_logs.menu_id'),
                'asc'
            )->orderBy(
                'temperature_logs.updated_at',
                'asc'
            );
        } else {

            $allowed = [
                'menu_id',
                'device_id',
                'sensor_id',
                'operator_id',
                'handy_no',
                'updated_at',
            ];

            if (! in_array($sort, $allowed)) {
                $sort = 'updated_at';
            }

            $query->orderBy($sort, $dir);
        }
        // ページネーション
        $perPage = intval($request->input('per_page', 20));

        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];

        $logs = $query->with(['menu', 'sensor', 'device', 'operator']) // ← 献立情報をロード
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Temperatures/Index', [
            'logs' => $logs,
            'tenants' => $tenants,
            'user' => $request->user(),
            'filters' => $request->only(['menu_id','sensor_id','device_id','operator_id','handy_no','per_page','sort_by','sort_dir']),
        ]);
    }
}
