<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue'; // バリデーションエラー表示用
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

// フォームのデータを管理するための useForm を使用
const form = useForm({
    company_name: '',
    zip_code: '',
    address: '',
    phone_number: '',
    representative_name: '',
    contact_person_name: '',
});

// フォーム送信時の処理
const submit = () => {
    form.post(route('customers.store'), { // customers.store ルートにPOSTリクエストを送信
        onFinish: () => form.reset(), // 送信完了後にフォームをリセット
    });
};
</script>

<template>
    <AppLayout title="新規顧客追加">
        <Head title="新規顧客追加" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                新規顧客追加
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

                            <div class="col-span-2"> <InputLabel for="address" value="住所" />
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
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                登録
                            </PrimaryButton>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>