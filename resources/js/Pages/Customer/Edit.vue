<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

// コントローラーから渡される 'customer' プロパティを受け取る
const props = defineProps({
    customer: Object, // 編集対象の顧客データ
});

// フォームのデータを管理するための useForm を使用
// 既存の顧客データでフォームを初期化
const form = useForm({
    company_name: props.customer.company_name,
    zip_code: props.customer.zip_code,
    address: props.customer.address,
    phone_number: props.customer.phone_number,
    representative_name: props.customer.representative_name,
    contact_person_name: props.customer.contact_person_name,
});

// フォーム送信時の処理（更新）
const submit = () => {
    form.put(route('customers.update', props.customer.id), { // customers.update ルートにPUTリクエストを送信
        onFinish: () => {}, // フォームリセットは不要
    });
};
</script>

<template>
    <AppLayout title="顧客情報編集">
        <Head title="顧客情報編集" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                顧客情報編集
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="company_name" value="会社名" />
                                <TextInput
                                    id="company_name"
                                    v-model="form.company_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.company_name" />
                            </div>

                            <div>
                                <InputLabel for="zip_code" value="郵便番号" />
                                <TextInput
                                    id="zip_code"
                                    v-model="form.zip_code"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.zip_code" />
                            </div>

                            <div class="col-span-2">
                                <InputLabel for="address" value="住所" />
                                <TextInput
                                    id="address"
                                    v-model="form.address"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.address" />
                            </div>

                            <div>
                                <InputLabel for="phone_number" value="電話番号" />
                                <TextInput
                                    id="phone_number"
                                    v-model="form.phone_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.phone_number" />
                            </div>

                            <div>
                                <InputLabel for="representative_name" value="代表者名" />
                                <TextInput
                                    id="representative_name"
                                    v-model="form.representative_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.representative_name" />
                            </div>

                            <div>
                                <InputLabel for="contact_person_name" value="担当者名" />
                                <TextInput
                                    id="contact_person_name"
                                    v-model="form.contact_person_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.contact_person_name" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                           <Link :href="route('customers.index')" class="text-gray-600 hover:text-gray-900 mr-4">
                                戻る
                            </Link>                        
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                更新
                            </PrimaryButton>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>