<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    username: String,
    token: String,
    tokenInvalid: Boolean,
});

// 変更点：NewPasswordController側は 'username' というキーで
// prop・バリデーションどちらも扱っているため、こちらも
// 'email' ではなく 'username' に統一する。
const form = useForm({
    token: props.token,
    username: props.username,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="パスワードの設定" />

    <AuthenticationCard>
        <template v-if="tokenInvalid">
            <p class="text-lg font-medium text-gray-900 mb-2">
                リンクが無効です
            </p>
            <p class="text-sm text-gray-600 mb-6">
                このパスワード再設定用のリンクは無効か、有効期限が切れています。<br>
                お手数ですが、再度パスワード再設定をお申し込みください。
            </p>
            <Link
                :href="route('password.request')"
                class="inline-block w-full text-center bg-[#1A2E2B] hover:bg-[#24403B] text-white rounded-md py-2 px-4"
            >
                パスワード再設定をお申し込みする
            </Link>
        </template>

        <template v-else>
            <p class="text-lg font-medium text-gray-900 mb-1">
                パスワードの設定
            </p>
            <p class="text-sm text-gray-600 mb-6">
                MyPageで使用するパスワードを設定してください。
            </p>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="username" value="ID" />
                    <TextInput
                        id="username"
                        v-model="form.username"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.username" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="新しいパスワード" />
                    <p class="text-xs text-gray-500 mt-1 mb-1">
                        ※ パスワードは8文字以上、半角英大文字・小文字・数字をすべて含めてご設定ください。
                    </p>
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password_confirmation" value="新しいパスワード(確認)" />
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <PrimaryButton
                    class="w-full justify-center mt-6 bg-[#1A2E2B] hover:bg-[#24403B] focus:ring-[#1A2E2B]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    パスワードを設定する
                </PrimaryButton>
            </form>
        </template>
    </AuthenticationCard>
</template>