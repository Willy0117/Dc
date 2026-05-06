<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class InsuranceSimulationController extends Controller
{
    // ★★★ シミュレーションの仮定する係数（実際はDBや設定ファイルから取得） ★★★

    // 完工高基本料率 (円/万円)
    private const COMPLETION_AMOUNT_BASE_RATE_PER_MILLION_YEN = 5.4;

    // 業種リスク係数
    private const INDUSTRY_RISK_FACTORS = [
        1 => 0.9,  // 低リスク
        2 => 1.1,  // 中リスク
        3 => 1.3,  // 高リスク
    ];

    // 支払限度額係数 (対人/対物)
    private const LIABILITY_LIMIT_FACTORS = [
        '1_1' => 0.9,
        '3_1' => 1.0,
        '5_3' => 1.15,
        'unlimited_5' => 1.3,
    ];

    // ★★★ 対人支払限度額の係数を独立して定義 ★★★
    private const PERSONAL_LIABILITY_FACTORS = [
        '1' => 0.8,        // 1億円
        '3' => 1.0,        // 3億円
        '5' => 1.15,       // 5億円
        'unlimited' => 1.3, // 無制限
    ];

    // ★★★ 対物支払限度額の係数を独立して定義 ★★★
    private const PROPERTY_LIABILITY_FACTORS = [
        '1' => 0.9,        // 1億円
        '3' => 1.05,       // 3億円
        '5' => 1.15,       // 5億円
    ];

    // 免責金額割引率
    private const DEDUCTIBLE_DISCOUNTS = [
        'none' => 1.0,
        '10' => 0.95,
        '30' => 0.90,
    ];

    // 特約追加料金 (固定料金のもの)
    private const SPECIAL_COVERAGE_ADDONS_FIXED = [
        'entrusted_property' => 10000, // 受託物賠償特約: 10,000円
    ];

    private const MANAGED_PROPERTY_PREMIUM_FACTOR = 3.75;

    // 新規追加：完成危険担保特約が有効な場合の乗数
    private const COMPLETED_OPERATIONS_PREMIUM_FACTOR = 1.6;

    /**
     * 建設業向け賠償責任保険料をシミュレーションします。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate(Request $request)
    {
        try {
            // 1. バリデーション
            $validatedData = $request->validate([
                'completion_amount_man' => 'required|integer|min:1',
                'industry_risk_level' => 'required|in:1,2,3',
                'personal_liability_limit_oku' => 'required|in:1,3,5,unlimited',
                'property_liability_limit_oku' => 'required|in:1,3,5',
                'deductible_amount_man' => 'required|in:none,10,30',
                'has_entrusted_property_coverage' => 'nullable|boolean',
                'has_completed_operations_coverage' => 'nullable|boolean',
            ]);

            // 2. 入力データの抽出と変換
            $completionAmountMan = (float)$validatedData['completion_amount_man'];
            $industryRiskLevel = (int)$validatedData['industry_risk_level'];
            $personalLiabilityLimit = $validatedData['personal_liability_limit_oku'];
            $propertyLiabilityLimit = $validatedData['property_liability_limit_oku'];
            $deductibleAmountMan = $validatedData['deductible_amount_man'];
            $hasEntrustedPropertyCoverage = $validatedData['has_entrusted_property_coverage'] ?? false;
            $hasCompletedOperationsCoverage = $validatedData['has_completed_operations_coverage'] ?? false;
           // ★★★ ここから変更 ★★★

            // 旧バージョンの liabilityLimitKey の計算は不要になるので削除またはコメントアウト
            // $liabilityLimitKey = '';
            // if ($personalLiabilityLimit === 'unlimited') {
            //     $liabilityLimitKey = 'unlimited_' . $propertyLiabilityLimit;
            // } else {
            //     $liabilityLimitKey = $personalLiabilityLimit . '_' . $propertyLiabilityLimit;
            // }
            
            $deductibleKey = $deductibleAmountMan;

            // --- 3. 保険料計算ロジック ---

            // Step 1: 基本保険料の算出
            $basePremium = $completionAmountMan * self::COMPLETION_AMOUNT_BASE_RATE_PER_MILLION_YEN;

            // Step 2: 業種（リスク度）による調整
            $industryRiskFactor = self::INDUSTRY_RISK_FACTORS[$industryRiskLevel] ?? 1.0;
            $adjustedByIndustryRisk = $basePremium * $industryRiskFactor;

            // 対人支払限度額と対物支払限度額による個別の調整
            // 以前の `$liabilityLimitFactor` と `$adjustedByLiabilityLimit` の計算を置き換えます
            $personalLiabilityFactor = self::PERSONAL_LIABILITY_FACTORS[$personalLiabilityLimit] ?? 1.0;
            $propertyLiabilityFactor = self::PROPERTY_LIABILITY_FACTORS[$propertyLiabilityLimit] ?? 1.0;

            // 両方の係数を乗じる
            $adjustedByLiabilityLimits = $adjustedByIndustryRisk * $personalLiabilityFactor * $propertyLiabilityFactor;

            // 完成危険担保特約による調整ロジック
            // ここでのベースは `$adjustedByLiabilityLimits` になります
            $premiumAfterCompletedOperationsAdjustment = $adjustedByLiabilityLimits; 
            if ($hasCompletedOperationsCoverage) {
                $premiumAfterCompletedOperationsAdjustment = $adjustedByLiabilityLimits * self::COMPLETED_OPERATIONS_PREMIUM_FACTOR;
            }

            // Step 4: 免責金額による調整
            $deductibleDiscount = self::DEDUCTIBLE_DISCOUNTS[$deductibleKey] ?? 1.0;
            $adjustedByDeductible = $premiumAfterCompletedOperationsAdjustment * $deductibleDiscount;

            // その他の固定料金特約による加算
            $specialCoverageAddonFixed = 0;
            if ($hasEntrustedPropertyCoverage) {
                // 管理下財物補償の保険料を比例で計算（完工高 × 係数）
                $specialCoverageAddonFixed += round($completionAmountMan * self::MANAGED_PROPERTY_PREMIUM_FACTOR);
            }

            // 最終保険料
            $finalPremium = $adjustedByDeductible + $specialCoverageAddonFixed;

            // 最低保険料の適用
            $minPremium = 10000;
            if ($finalPremium < $minPremium) {
                $finalPremium = $minPremium;
            }

            $finalPremium = round($finalPremium, 0);

            // 4. レスポンス
            return response()->json([
                'status' => 'success',
                'message' => '賠償責任保険料が計算されました。',
                'data' => [
                    'input' => $validatedData,
                    'calculated_premium_yen' => (int)$finalPremium,
                    'addon_amounts' => [
                        'completed_operations' => $hasCompletedOperationsCoverage
                            ? round($adjustedByLiabilityLimits * (self::COMPLETED_OPERATIONS_PREMIUM_FACTOR - 1), 0)
                            : 0,
                        'managed_property' => $hasEntrustedPropertyCoverage
                            ? round($completionAmountMan * self::MANAGED_PROPERTY_PREMIUM_FACTOR, 0)
                            : 0,
                    ],                    
                    'debug_info' => [
                        'base_premium' => round($basePremium, 2),
                        'adjusted_by_industry_risk' => round($adjustedByIndustryRisk, 2),
                        'adjusted_by_liability_limits' => round($adjustedByLiabilityLimits, 2),
                        'premium_after_completed_operations_adjustment' => round($premiumAfterCompletedOperationsAdjustment, 2),
                        'adjusted_by_deductible' => round($adjustedByDeductible, 2),
                        'special_coverage_addon_fixed' => $specialCoverageAddonFixed,
                        'personal_liability_factor' => $personalLiabilityFactor,
                        'property_liability_factor' => $propertyLiabilityFactor,
                        'derived_deductible_key' => $deductibleKey,
                        'completed_operations_factor_applied' => $hasCompletedOperationsCoverage ? self::COMPLETED_OPERATIONS_PREMIUM_FACTOR : 1.0,
                    ]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => '入力データの検証に失敗しました。',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error("保険料計算エラー: " . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'request' => $request->all()]);
            return response()->json([
                'status' => 'error',
                'message' => '予期せぬエラーが発生しました。',
            ], 500);
        }
    }
}
