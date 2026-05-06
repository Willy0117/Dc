<script setup>
// import AppLayout from '@/Layouts/AppLayout.vue'; // 認証済みユーザー用レイアウトの場合
import GuestLayout from '@/Layouts/GuestLayout.vue'; // ゲストユーザー用レイアウトの場合
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue'; // onMountedをインポート
import axios from 'axios';

// シミュレーションフォームの状態管理
const form = useForm({
    completion_amount_man: 10000, // 完工高（万円）初期値 - バックエンドに合わせてキー名変更
    industry_risk_level: 2, // 業種リスク 初期値 (バックエンドに合わせて数値に)
    personal_liability_limit_oku: '3', // 対人支払限度額 初期値 (文字列の数値)
    property_liability_limit_oku: '1', // 対物支払限度額 初期値 (文字列の数値)
    deductible_amount_man: 'none', // 免責金額 初期値 - バックエンドに合わせてキー名変更
    has_entrusted_property_coverage: false, // 受託物賠償特約 初期値
    has_completed_operations_coverage: false, // ★★★ 新規追加：完成危険担保特約 初期値 ★★★
});

// 計算結果のプレミアム
const calculatedPremium = ref(0);
const loading = ref(false);
const errors = ref({});
const addonAmounts = ref({
  completed_operations: 0,
  managed_property: 0,
});

// プレミアムを計算する非同期関数
const calculatePremium = async () => {
    loading.value = true;
    errors.value = {}; // エラーをクリア

    try {
        // バックエンドが期待するキー名に合わせてデータを送信
        const response = await axios.post('/api/calculate-premium', {
            completion_amount_man: form.completion_amount_man,
            industry_risk_level: form.industry_risk_level,
            personal_liability_limit_oku: form.personal_liability_limit_oku,
            property_liability_limit_oku: form.property_liability_limit_oku,
            deductible_amount_man: form.deductible_amount_man,
            has_entrusted_property_coverage: form.has_entrusted_property_coverage,
            has_completed_operations_coverage: form.has_completed_operations_coverage, // ★★★ 新規追加 ★★★
        });
        
        // バックエンドからのレスポンス構造に合わせて値をセット
        calculatedPremium.value = response.data.data.calculated_premium_yen;
        addonAmounts.value = response.data.data.addon_amounts;
    } catch (error) {
        console.error("Calculation error:", error);
        calculatedPremium.value = 0; // エラー時は0にリセット
        if (error.response && error.response.status === 422) {
            // Laravelのバリデーションエラーメッセージの構造を適切に処理
            errors.value = error.response.data.errors;
        } else {
            errors.value = { general: ["計算中に予期せぬエラーが発生しました。再度お試しください。"] }; // 配列で保持
        }
    } finally {
        loading.value = false;
    }
};

// フォームの変更を監視し、変更があったら計算を実行
// useFormのリアクティブなプロパティを直接監視することで、より効率的
watch(
    () => form.data(), // form.data() の最新のスナップショットを返す関数を監視
    // newData, oldData を比較する代わりに、formオブジェクトが変更されたら常にAPIを叩くシンプルアプローチ
    // これにより、UIの入力変更が即座に反映される
    () => {
        calculatePremium();
    },
    { deep: true } // オブジェクトの深い変更を監視
);

// コンポーネントがマウントされたときに初回計算を実行
onMounted(() => {
    calculatePremium();
});
</script>

<template>
    <GuestLayout title="保険料シミュレーション">
        <Head title="保険料シミュレーション" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                賠償責任保険料シミュレーション
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-6">シミュレーション条件入力</h3>

                    <div v-if="Object.keys(errors).length" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <p class="font-bold">入力エラー:</p>
                        <ul class="mt-2 list-disc list-inside">
                            <li v-for="(errorList, field) in errors" :key="field">
                                <span v-if="isNaN(field) && field !== 'general'">{{ field }}: </span>
                                <span v-for="err in errorList" :key="err">{{ err }}</span>
                            </li>
                            <li v-if="errors.general">{{ errors.general[0] }}</li>
                        </ul>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="completion_amount_man" class="block text-sm font-medium text-gray-700">直近1年間の完工高（万円）</label>
                            <input
                                type="number"
                                id="completion_amount_man"
                                v-model.number="form.completion_amount_man"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                min="100"
                                step="100"
                            >
                            <p class="mt-1 text-sm text-gray-500">（例: 10000で1億円）</p>
                        </div>

                        <div>
                            <label for="industry_risk_level" class="block text-sm font-medium text-gray-700">主な事業内容（リスク）</label>
                            <select
                                id="industry_risk_level"
                                v-model.number="form.industry_risk_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option :value="1">軽微な改修、内装工事など (低リスク)</option>
                                <option :value="2">一般建築、土木、電気工事など (中リスク)</option>
                                <option :value="3">大規模高所作業、特殊解体など (高リスク)</option>
                            </select>
                        </div>

                        <div>
                            <label for="personal_liability_limit_oku" class="block text-sm font-medium text-gray-700">対人支払限度額（億円）</label>
                            <select
                                id="personal_liability_limit_oku"
                                v-model="form.personal_liability_limit_oku"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="1">1億円</option>
                                <option value="3">3億円</option>
                                <option value="5">5億円</option>
                                <option value="unlimited">無制限</option>
                            </select>
                        </div>

                        <div>
                            <label for="property_liability_limit_oku" class="block text-sm font-medium text-gray-700">対物支払限度額（億円）</label>
                            <select
                                id="property_liability_limit_oku"
                                v-model="form.property_liability_limit_oku"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="1">1億円</option>
                                <option value="3">3億円</option>
                                <option value="5">5億円</option>
                            </select>
                        </div>

                        <div>
                            <label for="deductible_amount_man" class="block text-sm font-medium text-gray-700">免責金額（自己負担額）</label>
                            <select
                                id="deductible_amount_man"
                                v-model="form.deductible_amount_man"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="none">なし</option>
                                <option value="10">10万円</option>
                                <option value="30">30万円</option>
                            </select>
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <div class="flex items-center mb-4">
                                <input
                                    type="checkbox"
                                    id="entrusted_property_coverage"
                                    v-model="form.has_entrusted_property_coverage" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                >
                                <label for="entrusted_property_coverage" class="ml-2 block text-sm font-medium text-gray-700">
                                    管理下財物損壊担保特約（工事や作業中に第三者の財物（管理下財物を含む）を損壊した場合の補償）
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="completed_operations_coverage"
                                    v-model="form.has_completed_operations_coverage" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                >
                                <label for="completed_operations_coverage" class="ml-2 block text-sm font-medium text-gray-700">
                                    完成危険担保特約（引き渡し後の欠陥による損害を補償）
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-xl font-bold mb-4">概算保険料</h3>
                        <div class="text-4xl font-extrabold text-blue-600">
                            <span v-if="loading">計算中...</span>
                            <span v-else>{{ calculatedPremium.toLocaleString() }} 円</span>
                        </div>
                        <div class="mt-4 text-sm text-gray-700 space-y-1">
                            <div>管理下財物補償の加算額: <strong>{{ addonAmounts.managed_property.toLocaleString() }} 円</strong></div>
                            <div>完成危険担保特約の加算額: <strong>{{ addonAmounts.completed_operations.toLocaleString() }} 円</strong></div>
                        </div>

                        <p class="mt-4 text-sm text-gray-600">
                            ※ このシミュレーション結果はあくまで概算であり、実際の保険料とは異なる場合があります。正確な保険料は、別途お見積もりをご依頼ください。
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="() => {}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            この条件で見積もりを依頼する
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </GuestLayout>
</template>