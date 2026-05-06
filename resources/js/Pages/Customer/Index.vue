<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// コントローラーから渡される props を受け取る
const props = defineProps({
    customers: Object,
    filters: Object,
    filterOptions: Object,    
});

// usePage を使ってフラッシュメッセージを取得
const page = usePage();

// ★★★ 検索入力フィールドのモデルを定義 ★★★
const search = ref(props.filters.search || '');

// ★★★ ソート用のリアクティブ変数を定義 ★★★
const sortColumn = ref(props.filters.sort_by || 'company_name');
const sortDirection = ref(props.filters.sort_direction || 'asc');

// ★★★ 複数選択のためのリアクティブ変数を追加 ★★★
const selectedCustomers = ref([]);

// --- フィルター機能関連の変数定義（このブロックを早期に配置） ---
const showFilterPanel = ref(false); // フィルターパネルの表示状態を管理
const formFilters = ref({           // フィルターフォームの入力値を管理
    status: props.filters.status || '',
    industry: props.filters.industry || '',
    // 他のフィルター項目があればここに追加
});

// --- フィルター機能関連の関数定義（このブロックを早期に配置） ---
const openFilterPanel = () => {
    showFilterPanel.value = true;
};

const closeFilterPanel = () => {
    showFilterPanel.value = false;
};

const applyFilters = () => {
    router.get(route('customers.index'), {
        search: search.value,
        sort_by: sortColumn.value,
        sort_direction: sortDirection.value,
        ...formFilters.value, // フォームのフィルター条件を全て送る
    }, {
        preserveState: true,
        replace: true,
        onSuccess: () => {
            showFilterPanel.value = false; // フィルター適用後パネルを閉じる
        }
    });
};

const resetFilters = () => {
    formFilters.value.status = '';
    formFilters.value.industry = '';
    // 他のフィルター項目もここに追加
    applyFilters(); // リセット後、フィルターを適用
};
// --- フィルター機能関連の定義ここまで ---


// ★★★ ヘッダーチェックボックスの状態を管理するcomputedプロパティを追加 ★★★
const isAllSelected = computed(() => {
    if (props.customers.data.length === 0) {
        return false;
    }
    return props.customers.data.every(customer => selectedCustomers.value.includes(customer.id));
});

// ★★★ ヘッダーチェックボックスが部分的に選択されている状態を管理するcomputedプロパティを追加 ★★★
const isSomeSelected = computed(() => {
    return !isAllSelected.value && selectedCustomers.value.length > 0;
});

// ★★★ 全選択/全解除のトグル関数を追加 ★★★
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedCustomers.value = [];
    } else {
        selectedCustomers.value = props.customers.data.map(customer => customer.id);
    }
};

// ★★★ 検索入力値の変更を監視し、Inertiaリクエストを送信（applyFilters を呼び出すように変更） ★★★
watch(search, (value) => {
    selectedCustomers.value = []; // 検索条件が変わったら選択状態をリセット
    applyFilters(); // フィルター値も考慮してデータを再取得
});

// 顧客データが変わったときに選択状態を調整するwatchを追加（ページネーション移動時など）
watch(() => props.customers.data, () => {
    selectedCustomers.value = selectedCustomers.value.filter(id =>
        props.customers.data.some(customer => customer.id === id)
    );
});

// ★★★ 複数削除処理用の関数を追加 ★★★
const bulkDelete = () => {
    if (selectedCustomers.value.length === 0) {
        alert('削除する顧客を選択してください。');
        return;
    }

    if (confirm(`${selectedCustomers.value.length}件の顧客情報を削除してもよろしいですか？`)) {
        router.delete(route('customers.bulk-destroy'), {
            data: { ids: selectedCustomers.value },
            onSuccess: () => {
                selectedCustomers.value = [];
            },
            onError: (errors) => {
                alert('削除中にエラーが発生しました: ' + Object.values(errors).join('\n'));
            }
        });
    }
};

const sortBy = (column) => {
    let direction = 'asc';
    if (sortColumn.value === column && sortDirection.value === 'asc') {
        direction = 'desc';
    }

    sortColumn.value = column;
    sortDirection.value = direction;
    selectedCustomers.value = []; // ソート条件が変わったら選択状態をリセット

    applyFilters(); // router.get を削除し、代わりに applyFilters() を呼び出す
};

// ★★★ 現在のソート方向を示すアイコンを計算する算出プロパティ ★★★
const getSortArrow = (column) => {
    if (sortColumn.value === column) {
        return sortDirection.value === 'asc' ? ' ↑' : ' ↓';
    }
    return '';
};

const confirmAndDelete = (customerId, customerName) => {
    if (confirm(`「${customerName}」の顧客情報を削除してもよろしいですか？`)) {
        router.delete(route('customers.destroy', customerId), {
            onSuccess: () => {
                // 成功時の処理（自動的に一覧が更新され、フラッシュメッセージが表示される）
            },
            onError: (errors) => {
                alert('削除中にエラーが発生しました: ' + Object.values(errors).join('\n'));
            }
        });
    }
};
</script>

<template>
    <AppLayout title="顧客一覧">
        <Head title="顧客一覧" />
{{ $t('app.search') }}
{{ $t('app.search_placeholder') }}
{{ $t('app.add') }}

        <template #header>
            <div class="flex items-center justify-between mb-4"> <h2 class="text-2xl font-bold">顧客一覧</h2>
                <div class="flex items-center space-x-2">
                    <input
                        type="text"
                        v-model="search"
                        placeholder="会社名、代表者名、担当者名で検索..."
                        class="w-64 max-w-xs border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-sm"
                    />
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                            >
                                印刷
                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <a :href="route('customers.export.pdf')" target="_blank" rel="noopener noreferrer" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            PDF出力
                            </a>
                        </template>
                    </Dropdown>

                    <button
                        @click="bulkDelete"
                        :disabled="selectedCustomers.length === 0"
                        class="px-3 py-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap text-sm flex items-center" >
                        <i class="fas fa-trash-alt mr-1"></i> 削除 ({{ selectedCustomers.length }})
                    </button>

                    <Link :href="route('customers.create')" class="px-3 py-1.5 bg-primary-500 text-white rounded-md hover:bg-primary-600 whitespace-nowrap text-sm flex items-center"> <i class="fas fa-plus-circle mr-1"></i> 追加
                    </Link>

                    <button @click="openFilterPanel" class="px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-700 whitespace-nowrap text-sm flex items-center"> <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        フィルター
                    </button>
                </div>
            </div>
        </template>
          

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="page.props.flash && page.props.flash.success" class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded">
                        {{ page.props.flash.success }}
                    </div>

                    <div v-if="props.customers.data.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="p-3  flex items-center justify-center">
                                        <input
                                            type="checkbox"
                                            @click="toggleSelectAll" 
                                            :checked="isAllSelected"
                                            :indeterminate="isSomeSelected"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        />
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('company_name')">
                                        会社名 {{ getSortArrow('company_name') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('phone_number')">
                                        電話番号 {{ getSortArrow('phone_number') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('contact_person_name')">
                                        担当者名 {{ getSortArrow('contact_person_name') }}
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">編集</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="customer in props.customers.data" :key="customer.id">
                                    <td class="p-3 flex items-center justify-center">
                                        <input type="checkbox" :value="customer.id" v-model="selectedCustomers" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <Link :href="route('customers.show', customer.id)" class="text-blue-600 hover:text-blue-900">
                                            {{ customer.company_name }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ customer.phone_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ customer.contact_person_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('customers.edit', customer.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">編集</Link>
                                        <button @click="confirmAndDelete(customer.id, customer.company_name)" class="text-red-600 hover:text-red-900 focus:outline-none">
                                            削除
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <Pagination :links="props.customers.links" class="mt-4" />
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        まだ顧客が登録されていません。
                    </div>
                </div>
            </div>
        </div>

        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showFilterPanel" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40" @click="closeFilterPanel"></div>
        </transition>

        <transition
            enter-active-class="transition ease-out duration-300 transform"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in duration-200 transform"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div v-if="showFilterPanel" class="fixed top-0 right-0 w-80 bg-white shadow-lg h-full z-50 p-6 flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">フィルター</h3>
                    <button @click="closeFilterPanel" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex-grow">
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                        <select
                            id="status"
                            v-model="formFilters.status"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                        >
                            <option value="">すべて</option>
                            <option v-for="status in filterOptions.statuses" :key="status" :value="status">{{ status }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">業種</label>
                        <select
                            id="industry"
                            v-model="formFilters.industry"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                        >
                            <option value="">すべて</option>
                            <option v-for="industry in filterOptions.industries" :key="industry" :value="industry">{{ industry }}</option>
                        </select>
                    </div>

                </div>

                <div class="mt-auto flex justify-end space-x-3">
                    <button @click="resetFilters" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        リセット
                    </button>
                    <button @click="applyFilters" class="px-4 py-2 bg-primary-500 text-white rounded-md hover:bg-primary-600">
                        フィルターを適用
                    </button>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>