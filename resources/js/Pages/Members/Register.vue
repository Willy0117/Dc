<template>
  <GuestLayout>
    <Head title="会員登録（情報入力）" />

    <div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-6">会員情報入力</h2>

      <!-- フラッシュメッセージ -->
      <div v-if="props.flash?.success" class="mb-4 text-green-600">
        {{ props.flash.success }}
      </div>

      <form @submit.prevent="submitForm" class="space-y-8">

        <!-- 2カラム -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- 左カラム：会社情報 -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold mb-2">会社情報</h3>

            <div>
              <InputLabel value="会社名（フリガナ）" />
              <TextInput v-model="form.company_furigana" class="w-full" />
              <InputError :message="errors.company_furigana" />
            </div>

            <div>
              <InputLabel value="会社名" />
              <TextInput v-model="form.company_name" class="w-full" />
              <InputError :message="errors.company_name" />
            </div>

            <div>
              <InputLabel value="所在地 郵便番号" />
              <TextInput v-model="form.address_zip" class="w-full" placeholder="000-0000" />
              <InputError :message="errors.address_zip" />
            </div>

            <div>
              <InputLabel value="所在地 住所" />
              <TextInput v-model="form.address" class="w-full" />
              <InputError :message="errors.address" />
            </div>
          </div>

          <!-- 右カラム：代表者/担当者 -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold mb-2">代表者・担当者情報</h3>

            <div>
              <InputLabel value="代表者名（フリガナ）" />
              <TextInput v-model="form.representative_furigana" class="w-full" />
              <InputError :message="errors.representative_furigana" />
            </div>

            <div>
              <InputLabel value="代表者名" />
              <TextInput v-model="form.representative" class="w-full" />
              <InputError :message="errors.representative" />
            </div>

            <div>
              <InputLabel value="郵送先 郵便番号" />
              <TextInput v-model="form.post_zip" class="w-full" placeholder="000-0000" />
              <InputError :message="errors.post_zip" />
            </div>

            <div>
              <InputLabel value="郵送先 住所" />
              <TextInput v-model="form.post_address" class="w-full" />
              <InputError :message="errors.post_address" />
            </div>

            <div>
              <InputLabel value="TEL" />
              <TextInput v-model="form.tel" class="w-full" />
              <InputError :message="errors.tel" />
            </div>

            <div>
              <InputLabel value="FAX" />
              <TextInput v-model="form.fax" class="w-full" />
              <InputError :message="errors.fax" />
            </div>

            <div>
              <InputLabel value="担当者" />
              <TextInput v-model="form.staff" class="w-full" />
              <InputError :message="errors.staff" />
            </div>

            <div>
              <InputLabel value="携帯電話" />
              <TextInput v-model="form.mobile" class="w-full" placeholder="090-xxxx-xxxx" />
              <InputError :message="errors.mobile" />
            </div>
          </div>

        </div>

        <!-- PDF アップロード 2点 -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold">必要書類アップロード</h3>

          <!-- 履歴事項全部証明書 -->
          <div
            @dragover.prevent
            @dragenter.prevent
            @drop.prevent="handleDrop($event, 'history_certificate')"
            class="border-2 border-dashed border-gray-300 p-6 text-center cursor-pointer"
            @click="triggerFileSelect('history_certificate')"
          >
            <p>履歴事項全部証明書（PDF）をドラッグ＆ドロップ または クリックして選択</p>
            <input type="file" class="hidden" ref="historyCertificateInput" accept="application/pdf"
              @change="handleFileSelect($event, 'history_certificate')" />
          </div>
          <InputError :message="errors.history_certificate" />

          <!-- 口座振替依頼書 -->
          <div
            @dragover.prevent
            @dragenter.prevent
            @drop.prevent="handleDrop($event, 'bank_transfer_request')"
            class="border-2 border-dashed border-gray-300 p-6 text-center cursor-pointer"
            @click="triggerFileSelect('bank_transfer_request')"
          >
            <p>口座振替依頼書（PDF）をドラッグ＆ドロップ または クリックして選択</p>
            <input type="file" class="hidden" ref="bankTransferInput" accept="application/pdf"
              @change="handleFileSelect($event, 'bank_transfer_request')" />
          </div>
          <InputError :message="errors.bank_transfer_request" />
        </div>

        <!-- 送信 -->
        <PrimaryButton class="mt-6">送信する</PrimaryButton>

      </form>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const { props } = usePage();

// form データ
const form = ref({
  company_furigana: '',
  representative_furigana: '',
  company_name: '',
  representative: '',
  address_zip: '',
  address: '',
  post_zip: '',
  post_address: '',
  tel: '',
  fax: '',
  staff: '',
  mobile: '',
  history_certificate: null,
  bank_transfer_request: null,
});

// エラー
const errors = props.errors || {};

// PDF input ref
const historyCertificateInput = ref(null);
const bankTransferInput = ref(null);

// ドロップ処理
function handleDrop(event, type) {
  const file = event.dataTransfer.files[0];
  if (file && file.type === 'application/pdf') {
    form.value[type] = file;
  } else {
    alert('PDF ファイルのみアップロード可能です');
  }
}

// inputクリック
function triggerFileSelect(type) {
  if (type === 'history_certificate') historyCertificateInput.value.click();
  if (type === 'bank_transfer_request') bankTransferInput.value.click();
}

// ファイル選択
function handleFileSelect(event, type) {
  const file = event.target.files[0];
  if (file && file.type === 'application/pdf') {
    form.value[type] = file;
  } else {
    alert('PDF ファイルのみアップロード可能です');
  }
}

// 送信処理
function submitForm() {
  const data = new FormData();

  Object.keys(form.value).forEach(key => {
    if (form.value[key]) {
      data.append(key, form.value[key]);
    }
  });

  Inertia.post('/members/register/submit', data, {
    preserveScroll: true,
  });
}
</script>

