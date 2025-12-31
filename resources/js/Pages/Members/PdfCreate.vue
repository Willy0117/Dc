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
              <InputError :message="form.errors.company_furigana" />
            </div>

            <div>
              <InputLabel value="会社名" />
              <TextInput v-model="form.company_name" class="w-full" />
              <InputError :message="form.errors.company_name" />
            </div>

            <div>
              <InputLabel value="所在地 郵便番号" />
              <TextInput v-model="form.address_zip" class="w-full" placeholder="000-0000" />
              <InputError :message="form.errors.address_zip" />
            </div>

            <div>
              <InputLabel value="所在地 住所" />
              <TextInput v-model="form.address" class="w-full" />
              <InputError :message="form.errors.address" />
            </div>
          </div>

          <!-- 右カラム：代表者/担当者 -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold mb-2">代表者・担当者情報</h3>

            <div>
              <InputLabel value="代表者名（フリガナ）" />
              <TextInput v-model="form.representative_furigana" class="w-full" />
              <InputError :message="form.errors.representative_furigana" />
            </div>

            <div>
              <InputLabel value="代表者名" />
              <TextInput v-model="form.representative" class="w-full" />
              <InputError :message="form.errors.representative" />
            </div>

            <div>
              <InputLabel value="郵送先 郵便番号" />
              <TextInput v-model="form.post_zip" class="w-full" placeholder="000-0000" />
              <InputError :message="form.errors.post_zip" />
            </div>

            <div>
              <InputLabel value="郵送先 住所" />
              <TextInput v-model="form.post_address" class="w-full" />
              <InputError :message="form.errors.post_address" />
            </div>

            <div>
              <InputLabel value="TEL" />
              <TextInput v-model="form.tel" class="w-full" />
              <InputError :message="form.errors.tel" />
            </div>
        </div>
    <div class="p-6">
      <BankSelect v-model="selectedBankInfo" />

      <pre class="mt-4 bg-gray-100 p-2">
        {{ selectedBankInfo }}
      </pre>
    </div>


<div class="flex items-center gap-4">
  <div class="flex-1">
    <InputLabel value="銀行名" />
    <TextInput v-model="form.bank_name" class="w-full" />
    <InputError :message="form.errors.bank_name" />
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
    <InputError :message="form.errors.bank_type" />
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
            <InputError :message="form.errors.branch_name" />
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
            <InputError :message="form.errors.account_no" />
        </div>
        <div>
            <InputLabel value="口座名義（フリガナ）" />
            <TextInput v-model="form.account_kana" class="w-full" />
            <InputError :message="form.errors.account_kana" />
        </div>
        <div>
            <InputLabel value="口座名義" />
            <TextInput v-model="form.account_name" class="w-full" />
            <InputError :message="form.errors.account_name" />
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
import { Link, router, useForm,usePage } from '@inertiajs/vue3'
import { ref, toRef } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios'
import { useZipcode } from '@/composables/useZipcode'
import BankSelect from '@/Components/BankSelect.vue';

const page = usePage()

console.log(page.props) // ← ここで form が見える

const form = useForm({
  company_furigana: page.props.form?.company_furigana ?? '',
  representative_furigana: page.props.form?.representative_furigana ?? '',
  company_name: page.props.form?.company_name ?? '',
  representative: page.props.form?.representative ?? '',
  address_zip: page.props.form?.address_zip ?? '',
  address: page.props.form?.address ?? '',
  tel: page.props.form?.tel ?? '',
  bank_name: page.props.form?.bank_name ?? '',
  branch_name: page.props.form?.branch_name ?? '',
  account_type: page.props.form?.account_type ?? '普通',
  account_no: page.props.form?.account_no ?? '',
  account_kana: page.props.form?.account_kana ?? '',
  account_name: page.props.form?.account_name ?? '',
})


const selectedBankInfo = ref({});

const errors = ref({});

const submit = async () => {
  errors.value = {}

  // 必須チェック
  const requiredFields = [
    'company_furigana', 'representative_furigana', 'company_name', 'representative',
    'address_zip', 'address', 'post_zip', 'post_address', 'tel',
    'bank_name', 'branch_name', 'bank_type', 'account_type',
    'account_no', 'account_kana', 'account_name'
  ]

  requiredFields.forEach(field => {
    if (!form[field] || form[field].trim() === '') {
      errors.value[field] = '必須項目です'
    }
  })

  // エラーがある場合は送信せず return
  if (Object.keys(errors.value).length > 0) {
    console.log('入力エラー:', errors.value)
    return
  }

  // 必要なキーだけを抜き出して送信
  const payload = { ...form }
  
  try {
    const res = await axios.post('/members/pdfgenerate', payload)
    console.log('PDF 作成成功', res.data)
    if (res.data.url) {
        router.get(route('members.pdf.preview'), {
          pdfUrl: res.data.url,
        })
    }
  } catch (e) {
    if (e.response && e.response.status === 422) {
      console.log(e.response.data.errors);
    }
    console.error('PDF 作成失敗', e)
  }
}

useZipcode(
  toRef(form, 'post_zip'),
  toRef(form, 'post_address')
)

useZipcode(
  toRef(form, 'address_zip'),
  toRef(form, 'address')
)

</script>
