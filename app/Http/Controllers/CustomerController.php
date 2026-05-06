<?php

namespace App\Http\Controllers;

use App\Models\Customer; // ★★★ この行を追加 ★★★
use Illuminate\Http\Request;
use Inertia\Inertia; // ★★★ この行があることを確認 ★★★
use Illuminate\Support\Facades\DB;
use PDF;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         // クエリビルダを初期化
        $customers = Customer::query();

        // 検索機能の追加
        if ($request->has('search')) {
            $search = $request->input('search');
            $customers->where(function ($query) use ($search) {
                $query->where('company_name', 'like', '%' . $search . '%')
                      ->orWhere('representative_name', 'like', '%' . $search . '%')
                      ->orWhere('contact_person_name', 'like', '%' . $search . '%');
            });
        }

        // フィルター機能の追加 (既存コード)
        if ($request->filled('status')) {
            $customers->where('status', $request->input('status'));
        }
        if ($request->filled('industry')) {
            $customers->where('industry', $request->input('industry'));
        }

        // ★★★ ソート機能の追加 ★★★
        // デフォルトのソートカラムと方向を設定
        $sortColumn = $request->input('sort_by', 'company_name'); // デフォルトは会社名
        $sortDirection = $request->input('sort_direction', 'asc'); // デフォルトは昇順

        // ソート可能なカラムのホワイトリスト
        $allowedSortColumns = [
            'company_name',
            'phone_number',
            'address', // 住所もソート対象に追加
            'contact_person_name',
        ];

        // リクエストされたソートカラムが許可されているか確認
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'company_name'; // 許可されていない場合はデフォルトに戻す
        }
        // ソート方向が 'asc' または 'desc' か確認
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc'; // 許可されていない場合はデフォルトに戻す
        }

        $customers->orderBy($sortColumn, $sortDirection);


        // ページネーションを適用してデータを取得
        $customers = $customers->paginate(20)->withQueryString();

        // ★★★ この部分が今回追加・修正が必要な箇所です ★★★
        // フロントエンドに渡すフィルターオプションを準備
        $allStatuses = [
            'アクティブ', // 例: 実際にあるステータス名に合わせる
            '休眠',
            '見込み',
            // 必要に応じてさらにステータスを追加
        ];
        $allIndustries = [
            'IT', // 例: 実際にある業種名に合わせる
            '製造業',
            'サービス業',
            '小売業',
            // 必要に応じてさらに業種を追加
        ];

        return Inertia::render('Customer/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction', 'status', 'industry']),
            'filterOptions' => [ // このキー名と値の構造が Vue コンポーネントの props と一致します
                'statuses' => $allStatuses,
                'industries' => $allIndustries,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 'Customer/Create' Vueコンポーネントをレンダリングするだけ
        return Inertia::render('Customer/Create');
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            // 送信されたデータをバリデーション
        $validatedData = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:8'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'representative_name' => ['nullable', 'string', 'max:255'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
        ]);

        // バリデーションが通ったら、新しい顧客レコードを作成して保存
        Customer::create($validatedData);

        // 顧客一覧ページにリダイレクトし、成功メッセージをフラッシュセッションに保存
        return redirect()->route('customers.index')->with('success', '顧客が正常に追加されました。');
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        // 'Customer/Show' Vueコンポーネントに顧客データを渡してレンダリング
        return Inertia::render('Customer/Show', [
            'customer' => $customer,
        ]);        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        // 'Customer/Edit' Vueコンポーネントに顧客データを渡してレンダリング
        return Inertia::render('Customer/Edit', [
            'customer' => $customer,
        ]);        
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        // 送信されたデータをバリデーション
        $validatedData = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:8'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'representative_name' => ['nullable', 'string', 'max:255'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
        ]);

        // 顧客情報を更新
        $customer->update($validatedData);

        // 顧客一覧ページにリダイレクトし、成功メッセージをフラッシュセッションに保存
        return redirect()->route('customers.index')->with('success', '顧客情報が正常に更新されました。');
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // 顧客を削除
        $customer->delete();

        // 顧客一覧ページにリダイレクトし、成功メッセージをフラッシュセッションに保存
        return redirect()->route('customers.index')->with('success', '顧客が正常に削除されました。');
        //
    }
    //

    // ★★★ 複数削除用の新しいメソッドを追加 ★★★
    /**
     * Remove multiple resources from storage.
     * 複数の顧客をデータベースから一括で削除します。
     */
    public function bulkDestroy(Request $request)
    {
        // リクエストからIDの配列を取得
        $ids = $request->input('ids');

        // 渡されたIDが配列であることを確認し、空でないかチェック
        if (is_array($ids) && !empty($ids)) {
            // whereIn を使って、指定されたIDに一致するすべての顧客を削除
            Customer::whereIn('id', $ids)->delete();

            // 成功メッセージと共に一覧ページにリダイレクト
            return redirect()->route('customers.index')->with('success', count($ids) . '件の顧客が正常に削除されました。');
        }

        // IDが指定されていない場合はエラーメッセージを表示
        return redirect()->route('customers.index')->with('error', '削除する顧客が選択されていません。');
    } 
    /**
     * 顧客一覧をPDFとして出力する
     *
     * @return \Illuminate\Http\Response
     */
public function exportPdf()
    {
        $customers = Customer::all();
        //$pdf = PDF::loadView('customers.pdf', compact('customers'));
        //　横向けに出力する場合
        $pdf = PDF::loadView('customers.pdf', compact('customers'))
          ->setPaper('a4', 'landscape');  // 横向きに設定
        // シンプルにファイル名だけ指定
        return $pdf->stream('customer_list.pdf');
    }

}
