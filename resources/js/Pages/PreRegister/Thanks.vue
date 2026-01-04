<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  email: String
})
</script>

<template>
  <GuestLayout>

    <div class="max-w-md mx-auto mt-16 text-center">
        <div class="text-green-600 text-3xl mb-4">✔</div>

        <h1 class="text-xl font-bold mb-2">
        メール送信ありがとうございました
        </h1>

        <p class="text-gray-600 mb-6">
        登録いただいたメールアドレス <strong>{{ email }}</strong> に本登録用のURLを送信いたしました。<br>
        そちらから本登録をお願いいたします。
        </p>
    </div>
    
  </GuestLayout>
</template>
