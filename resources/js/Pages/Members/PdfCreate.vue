<template>
  <GuestLayout>
    <Head title="会員登録（情報入力）" />    
    <div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-6">口座振替申請書作成</h2>
    <form @submit.prevent="submit">

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
        </div>
<div class="flex items-center gap-4">
  <div class="flex-1">
    <InputLabel value="銀行名" />
    <TextInput v-model="form.bank_name" class="w-full" />
    <InputError :message="errors.bank_name" />
  </div>

  <div class="w-64">
    <InputLabel value="銀行種別" />
    <select v-model="form.bank_type" class="border p-2 w-full rounded">
      <option value="">選択してください</option>
      <option value="銀行">銀行</option>
      <option value="信用金庫">信用金庫</option>
      <option value="信用組合">信用組合</option>
      <option value="農業協同組合">農業協同組合</option>
    </select>
    <InputError :message="errors.bank_type" />
  </div>
</div>
<!--
        <div>
            <InputLabel value="銀行名" />
            <TextInput v-model="form.bank_name" class="w-full" />
            <InputError :message="errors.bank_name" />
        </div>
-->
        <div>
            <InputLabel value="支店名" />
            <TextInput v-model="form.branch_name" class="w-full" />
            <InputError :message="errors.branch_name" />
        </div>

        <div>
        <InputLabel value="口座種別（普通 / 当座）" />
        <select v-model="form.account_type" class="border p-2 w-full rounded">
            <option value="普通">普通</option>
            <option value="当座">当座</option>
        </select>
        </div>

        <div>
            <InputLabel value="口座番号" />
            <TextInput v-model="form.account_no" class="w-full" />
            <InputError :message="errors.account_no" />
        </div>
        <div>
            <InputLabel value="口座名義（フリガナ）" />
            <TextInput v-model="form.account_kana" class="w-full" />
            <InputError :message="errors.account_kana" />
        </div>
        <div>
            <InputLabel value="口座名義" />
            <TextInput v-model="form.account_name" class="w-full" />
            <InputError :message="errors.account_name" />
        </div>

      <button
        type="submit"
        class="bg-blue-600 text-white px-4 py-2 rounded"
      >PDF作成</button>

    </div>
    </form>

    <div v-if="pdfUrl" class="mt-5">
      <h2 class="text-lg font-bold mb-2">PDFが生成されました</h2>

      <iframe :src="pdfUrl" width="100%" height="500px"></iframe>

      <a :href="pdfUrl" download class="text-blue-600 underline">
        ダウンロード
      </a>
    </div>

  </div>
  </GuestLayout>
</template>

<script setup>
import { ref } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios'

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
  bank_name: '',
  branch_name: '',
  account_type : '普通',
  account_no : '',
  account_kana : '',
  account_name : '',
})
const errors = ref({});

const submit = async () => {
    console.log(form)
  try {
    const res = await axios.post('/members/pdfgenerate', form.value)
    console.log('PDF 作成成功', res.data)
    if (res.data.url) {
        window.open(res.data.url, '_blank');   // ← PDF を表示
    }
  } catch (e) {
    console.error('PDF 作成失敗', e)
  }
}
</script>
