<template>
  <GuestLayout>
    <Head :title="t('register.title')" />

    <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-6">{{ t('register.title') }}</h2>

      <form @submit.prevent="submitForm" class="space-y-6">

        <!-- 誓約書チェック -->
        <Agree v-model="form.agree" :error="errors.agree" />

        <!-- 加盟団体 -->
        <AffiliatesList v-model="form.affiliate" />

        <!-- エラー表示 -->
        <div v-if="errors.affiliate" class="text-red-500 text-sm">
          {{ errors.affiliate }}
        </div>

        <!-- flash メッセージ -->
        <div v-if="flash.success" class="text-green-600 mt-2">
          {{ flash.success }}
        </div>

        <PrimaryButton class="mt-4">
          次へ進む
        </PrimaryButton>

      </form>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref , computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import Agree from '@/Components/Agree.vue';
import AffiliatesList from '@/Components/AffiliatesList.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const { t } = useI18n();

// props を取得
const { props } = usePage();

const flash = props.flash || {};
const errors = props.errors || {};

// token を安全に取得
const token = props.token;

// フォーム
const form = ref({
  agree: false,
  agree_at: null,
  affiliate: null
});

// 送信
const submitForm = () => {
  if (!form.value.agree) {
    alert('誓約書に同意してください');
    return;
  }

  // 加盟団体チェック
  if (!form.value.affiliate) {
    alert('加盟団体の選択をしてください');
    return;
  }

  // 加盟済みの場合は Rejected ページへ遷移
  if (form.value.affiliate === 'yes') {
    router.visit(route('members.register.rejected', { token }));
    return;
  }
  // 同意日時をセット
  form.value.agree_at = new Date().toISOString();
  // 加盟団体に加入している場合
  router.post(
    route('members.register.agree', { token }),
    form.value,
    {
      onSuccess: () => {
        // Registory ページへ遷移
        router.visit(route('members.register.register', { token }));
      },
      onError: (errors) => {
        console.log(errors);
        alert('入力にエラーがあります');
      }
    }
  );
};
</script>