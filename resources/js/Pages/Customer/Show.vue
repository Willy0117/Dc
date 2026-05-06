<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3'; // Link もインポート

// コントローラーから渡される 'customer' プロパティを受け取る
const props = defineProps({
    customer: Object, // 表示対象の顧客データ
});
</script>

<template>
    <AppLayout :title="`顧客詳細: ${props.customer.company_name}`">
        <Head :title="`顧客詳細: ${props.customer.company_name}`" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                顧客詳細: {{ props.customer.company_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="mb-4 flex justify-between items-center">
                        <Link :href="route('customers.index')" class="text-blue-500 hover:text-blue-700">
                            &larr; 顧客一覧に戻る
                        </Link>
                        <Link :href="route('customers.edit', props.customer.id)" class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600">
                            編集
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="col-span-2">
                            <p class="font-bold text-gray-700">会社名:</p>
                            <p class="text-gray-900">{{ props.customer.company_name }}</p>
                        </div>
                        <div>
                            <p class="font-bold text-gray-700">郵便番号:</p>
                            <p class="text-gray-900">{{ props.customer.zip_code || '未設定' }}</p>
                        </div>
                        <div>
                            <p class="font-bold text-gray-700">電話番号:</p>
                            <p class="text-gray-900">{{ props.customer.phone_number || '未設定' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="font-bold text-gray-700">住所:</p>
                            <p class="text-gray-900">{{ props.customer.address || '未設定' }}</p>
                        </div>
                        <div>
                            <p class="font-bold text-gray-700">代表者名:</p>
                            <p class="text-gray-900">{{ props.customer.representative_name || '未設定' }}</p>
                        </div>
                        <div>
                            <p class="font-bold text-gray-700">担当者名:</p>
                            <p class="text-gray-900">{{ props.customer.contact_person_name || '未設定' }}</p>
                        </div>
                        <div>
                            <p class="font-bold text-gray-700">登録日時:</p>
                            <p class="text-gray-900">{{ new Date(props.customer.created_at).toLocaleString() }}</p>
                        </div>
                        <div>
                            <p class="font-bold text-gray-700">最終更新日時:</p>
                            <p class="text-gray-900">{{ new Date(props.customer.updated_at).toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>