<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\Tenant;
use App\Models\User; // ← これを追加！
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MenuController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Menu::query();
        // テナント絞り込み（Super Admin は全件表示）
        if (!$user->hasRole('Super Admin')) {
            $query->where('tenant_id', $user->tenant_id);
        }
        if ($dish_name = $request->input('dish_name')) {
            $query->where('dish_name', 'like', "%{$dish_name}%");
        }
        if ($process = $request->input('process')) {
            $query->where('process', 'like', "%{$process}%");
        }

        // 日付検索
        if ($serving_date_from = $request->input('serving_date_from')) {
            $query->where('serving_date', '>=', $serving_date_from);
        }
        if ($serving_date_to = $request->input('serving_date_to')) {
            $query->where('serving_date', '<=', $serving_date_to);
        }
        if ($cooking_date_from = $request->input('cooking_date_from')) {
            $query->where('cooking_date', '>=', $cooking_date_from);
        }
        if ($cooking_date_to = $request->input('cooking_date_to')) {
            $query->where('cooking_date', '<=', $cooking_date_to);
        }
        // ソート
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // ページあたり件数
        $perPage = intval($request->input('per_page', 10));

        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];                     

        $menus = Menu::query()
            ->when($request->dish_name, fn($q, $v) => $q->where('dish_name', 'like', "%$v%"))
            ->when($request->process, fn($q, $v) => $q->where('process', 'like', "%$v%"))
            ->when($request->serving_date_from, fn($q, $v) => $q->where('serving_date', '>=', $v))
            ->when($request->serving_date_to, fn($q, $v) => $q->where('serving_date', '<=', $v))
            ->when($request->cooking_date_from, fn($q, $v) => $q->where('cooking_date', '>=', $v))
            ->when($request->cooking_date_to, fn($q, $v) => $q->where('cooking_date', '<=', $v))
            ->orderBy($request->sort_by ?? 'id', $request->sort_dir ?? 'asc')
            ->paginate($perPage)
            ->withQueryString(); // 検索条件をページリンクに保持


        return Inertia::render('Menus/Index', [
            'menus' => $menus,
            'tenants' => $tenants,
            'user' => $user, // Vue 側で判定に必要
            'filters' => $request->only([
                'serving_date_from', 'serving_date_to',
                'cooking_date_from', 'cooking_date_to',
                'dish_name', 'process', 'per_page', 'sort_by', 'sort_dir'
            ]),
            'redirect_to' => '',
        ]);
    }

    // Create 画面
    public function create(Request $request)
    {
        $user = $request->user();

        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];                     

        $menu = null;

        // コピー用モードの場合
        if ($request->input('mode') === 'copy' && $menu_id = $request->input('menu_id')) {
            $menu = Menu::find($menu_id);
        }

        return Inertia::render('Menus/Create', [
            'filters' => $request->only([
                'serving_date_from', 'serving_date_to',
                'cooking_date_from', 'cooking_date_to',
                'dish_name', 'process', 'per_page', 'sort_by', 'sort_dir','page'
            ]),
            'menu' => $menu, // コピー元のデータを渡す
            'tenants' => $tenants,
            'user' => $user, // Vue 側で判定に必要
            'redirect_to' => $request->query(
                'redirect_to',
                route('menus.index')),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'serving_date' => ['required','date'],
            'serving_time' => ['required','string'],
            'dish_name' => ['required','string'],
            'process' => ['nullable','string'],
            'materials' => ['nullable','string'],
            'cooking_date' => ['required','date'],
            'tenant_id' => ['nullable', 'exists:tenants,id'], 
        ], [
            'serving_date.required' => __('validation.required', ['attribute' => __('配膳日')]),
            'dish_name.required' => __('validation.required', ['attribute' => __('料理名')]),
            'cooking_date.required' => __('validation.required', ['attribute' => __('調理日')]),
        ]);
        // tenant_id を設定（Super Admin は選択、Tenant Admin は自動）
        $validated['tenant_id'] = $user->hasRole('Super Admin') 
            ? $validated['tenant_id'] 
            : $user->tenant_id;

        Menu::create($validated);

        $redirectTo = $request->input('redirect_to');

        if ($redirectTo) {
            // weekly など明示的な戻り先が指定されている場合
            return redirect($redirectTo)
                ->with('success', __('menu has been updated.'));
        }

        // 従来どおり：通常一覧 + filters
        return redirect()->route('menus.index', $request->only([
            'serving_date_from', 'serving_date_to',
            'cooking_date_from', 'cooking_date_to',
            'dish_name', 'process', 'equipment_name', 'measurement_device',
            'per_page', 'sort_by', 'sort_dir', 'page',
        ]))->with('success', __('menu has been updated.'));
    }

    public function edit(Request $request, Menu $menu)
    {
        $user = $request->user();

        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];                     

        return Inertia::render('Menus/Edit', [
            'menu' => $menu,
            'tenants' => $tenants,
            'user' => $user, // Vue 側で判定に必要
            'filters' => $request->only([
                'serving_date_from', 'serving_date_to',
                'cooking_date_from', 'cooking_date_to',
                'dish_name', 'process', 'per_page', 'sort_by', 'sort_dir','page'
            ]),
            'redirect_to' => $request->query(
                'redirect_to',
                route('menus.index')),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $user = $request->user();

        $validated = $request->validate([
            'serving_date' => ['required','date'],
            'serving_time' => ['required','string'],
            'dish_name' => ['required','string'],
            'process' => ['nullable','string'],
            'materials' => ['nullable','string'],
            'cooking_date' => ['required','date'],
            'tenant_id' => ['nullable', 'exists:tenants,id'], 
        ], [
            'serving_date.required' => __('validation.required', ['attribute' => __('配膳日')]),
            'dish_name.required' => __('validation.required', ['attribute' => __('料理名')]),
            'cooking_date.required' => __('validation.required', ['attribute' => __('調理日')]),
        ]);
        // tenant_id を設定（Super Admin は選択、Tenant Admin は自動）
        $validated['tenant_id'] = $user->hasRole('Super Admin') 
            ? $validated['tenant_id'] 
            : $user->tenant_id;

        $menu->update($validated);

        $redirectTo = $request->input('redirect_to');

        if ($redirectTo) {
            // weekly など明示的な戻り先が指定されている場合
            return redirect($redirectTo)
                ->with('success', __('menu has been updated.'));
        }

        // 従来どおり：通常一覧 + filters
        return redirect()->route('menus.index', $request->only([
            'serving_date_from', 'serving_date_to',
            'cooking_date_from', 'cooking_date_to',
            'dish_name', 'process',
            'per_page', 'sort_by', 'sort_dir', 'page',
        ]))->with('success', __('menu has been updated.'));
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', __('menu has been deleted.'));
    }

    public function bulkDelete(Request $request)
    {
        Menu::whereIn('id', $request->ids)->delete();
        return redirect()->route('menus.index')->with('success', __('Selected menus have been deleted.'));
    }

    public function showImportForm()
    {
        return Inertia::render('Menus/Import');
    }

    public function importExcel(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '認証されていません'], 401);
        }

        $tenantId = $user->tenant_id ?? 1;
        $menus = $request->menus ?? [];

        foreach ($menus as $m) {
            Menu::create([
                'tenant_id' => $tenantId,
                'dish_name' => $m['dish_name'],
                'serving_date' => $m['serving_date'],
                'serving_time' => $m['serving_time'],
                'cooking_date' => $m['cooking_date'] ?? $m['serving_date'],
                'materials' => null,
                'disabled' => 1,
                'display_order' => 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '献立データを保存しました',
        ]);
    }
/*    
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        if (!$file) {
            return response()->json(['error' => 'ファイルが見つかりません'], 400);
        }

        $tenantId = Auth::user()->tenant_id;

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        // 配膳日セルの位置（固定）
        $servingCols = ['D', 'M', 'V', 'AE', 'AN', 'AW', 'BF'];
        $servingDates = [];
        foreach ($servingCols as $col) {
            $cell = $sheet->getCell($col . '6')->getValue();
            if ($cell) {
                // 「11/2(月)」の形式を Y-m-d に変換
                $dateStr = preg_replace('/\(.+\)/', '', $cell);
                try {
                    $servingDates[$col] = Carbon::parse($dateStr)->format('Y-m-d');
                } catch (\Exception $e) {
                    $servingDates[$col] = null;
                }
            } else {
                $servingDates[$col] = null;
            }
        }

        // データ行は7行目から開始
        $startRow = 7;
        $highestRow = $sheet->getHighestRow();

        for ($row = $startRow; $row <= $highestRow; $row++) {
            $mealType = $sheet->getCell('B' . $row)->getValue();
            if (!$mealType) continue;

            foreach ($servingCols as $col) {
                $menuCell = $sheet->getCell($col . $row)->getValue();
                if (!$menuCell) continue;

                // dish_name = B列（食事区分） + 献立メニュー
                $dishName = trim($mealType . ' ' . $menuCell);

                // 配膳日
                $servingDate = $servingDates[$col];
                if (!$servingDate) continue;

                // 調理日は列 offset で K列や T列など
                $cookingCol = $this->getCookingCol($col); // 下記で定義
                $cookingCell = $sheet->getCell($cookingCol . $row)->getValue();

                $cookingDate = null;
                if ($cookingCell !== null && $cookingCell !== '') {
                    try {
                        if (is_numeric($cookingCell)) {
                            $cookingDate = ExcelDate::excelToDateTimeObject($cookingCell)->format('Y-m-d');
                        } else {
                            $cookingDate = Carbon::parse($cookingCell)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $cookingDate = $servingDate;
                    }
                } else {
                    $cookingDate = $servingDate;
                }

                // serving_date と同じなら cooking_date を null にする
                if ($cookingDate === $servingDate) {
                    $cookingDate = null;
                }

                // 配膳時間判定
                $servingTime = $this->getServingTime($mealType);

                // DB登録
                Menu::create([
                    'tenant_id' => $tenantId,
                    'dish_name' => $dishName,
                    'serving_date' => $servingDate,
                    'serving_time' => $servingTime,
                    'cooking_date' => $cookingDate,
                    'materials' => null,
                    'disabled' => 1,
                    'display_order' => 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '献立データをインポートしました',
            'fileName' => $file->getClientOriginalName()
        ]);
    }
*/
    /**
     * 配膳日列から調理日列を返す
     */
    private function getCookingCol(string $servingCol): string
    {
        // D→K, M→T, V→AC, AE→AL, AN→AU, AW→BD, BF→BM
        $map = [
            'D' => 'K', 'M' => 'T', 'V' => 'AC', 'AE' => 'AL',
            'AN' => 'AU', 'AW' => 'BD', 'BF' => 'BM'
        ];
        return $map[$servingCol] ?? $servingCol;
    }

    /**
     * 食事区分から配膳時間を返す
     */
    private function getServingTime(string $mealType): string
    {
        if (preg_match('/おやつ\((\d+)\)/u', $mealType, $matches)) {
            return sprintf('%02d:00:00', $matches[1]);
        } elseif (str_contains($mealType, '朝')) {
            return '08:00:00';
        } elseif (str_contains($mealType, '昼')) {
            return '12:00:00';
        } elseif (str_contains($mealType, '夕')) {
            return '18:00:00';
        } else {
            return '00:00:00';
        }
    }


    public function importExcelStore(Request $request)
    {
        // アップロード処理など
    }

    public function weekly(Request $request)
    {
        // weekStart が渡されていればそれを基準に、なければ今週
        $weekStart = $request->input('weekStart');
        $startDate = $weekStart ? Carbon::parse($weekStart) : now()->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->addDays(6);

        // メニューを取得し「日付 → 時間 → 配列」に変換
        $menus = Menu::whereBetween('serving_date', [$startDate, $endDate])
            ->orderBy('serving_time')
            ->get()
            ->groupBy(function ($menu) {
                return $menu->serving_date->toDateString();
            })
            ->map(function ($dayGroup) {
                return $dayGroup->groupBy(function ($menu) {
                    return Carbon::parse($menu->serving_time)->format('H:i'); // 秒削除
                });
            });

        // distinct で使用する時間も同様に "HH:MM" に揃える
        $servingTimes = Menu::whereBetween('serving_date', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(serving_time, '%H:%i') as serving_time")
            ->distinct()
            ->orderBy('serving_time')
            ->pluck('serving_time');

        return Inertia::render('Menus/Weekly', [
            'menuData' => $menus,
            'servingTimes' => $servingTimes,
            'weekStart' => $startDate->toDateString(),
            'redirect_to' => route('menus.weekly', ['weekStart' => $startDate->toDateString()]),
        ]);
    }
    
    public function autocomplete(Request $request)
    {
        $search = $request->input('q');

        $menus = Menu::query()
            ->when($search, fn($q) => $q->where('dish_name', 'like', "%{$search}%"))
            ->orderBy('serving_date', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'label' => "{$m->dish_name} ({$m->serving_date})"
            ]);

        return response()->json($menus);
    }

}