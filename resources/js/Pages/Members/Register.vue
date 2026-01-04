<template>
  <GuestLayout>
    <Head title="会員登録（情報入力）" />

    <div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-6">会員情報入力</h2>

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
              <TextInput v-model="form.address_zip" placeholder="000-0000" @keydown.enter.prevent />
              
              <!-- 候補が2件以上ある場合は選択させる -->
              <ul v-if="candidates && candidates.length > 1" class="border mt-1 p-2 bg-white">
                <li
                  v-for="(c, i) in candidates"
                  :key="i"
                  @click="selectCandidate(c)"
                  class="cursor-pointer hover:bg-gray-100 p-1 rounded"
                >
                  {{ c.label }}
                </li>
              </ul>

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
<!--
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
-->
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
        <!-- 2カラム -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- 左カラム -->
          <div class="space-y-4 max-w-xl mx-auto p-2">

            <!-- 銀行選択 -->
            <InputLabel value="銀行選択" />
            <div class="grid grid-cols-4 gap-2 mb-3">
              <button
                v-for="c in bankCategories"
                :key="c.id"
                type="button"
                @click="selectCategory(c)"
                :class="[
                  'px-4 py-1 rounded border text-sm',
                  selectedCategory === c.value
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-white text-gray-700 hover:bg-gray-100'
                ]"
              >
                {{ c.label }}
              </button>
            </div>

            <!-- 銀行名 + 銀行コード -->
            <div class="grid grid-cols-2 gap-4 mb-2">
              <div>
                <Autocomplete
                  :key="bankKey"
                  v-model="selectedBank"
                  label="銀行名"
                  fetch-url="/api/banks"
                  :extra-params="form.bank_type ? { category: form.bank_type } : {}"
                  :initial="form.bank_name"
                  @selected="handleBankSelected"
                />
                <p v-if="errors.bank_name" class="text-red-500 text-sm mt-1">
                  {{ errors.bank_name }}
                </p>
              </div>
              <div>
                <InputLabel value="銀行コード" />
                <TextInput v-model="form.bank_code" class="w-full" />
              </div>
            </div>

            <!-- 支店名 + 支店コード -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Autocomplete
                  v-if="form.bank_code"
                  :model-value="selectedBranch"
                  label="支店名"
                  fetch-url="/api/branches"
                  :extra-params="{ bank_code: form.bank_code }"
                  :initial="form.branch_name"
                  @selected="handleBranchSelected"
                />
                <p v-if="errors.branch_name" class="text-red-500 text-sm mt-1">
                  {{ errors.branch_name }}
                </p>
              </div>
              <div>
                <InputLabel value="支店コード" />
                <TextInput v-model="form.branch_code" class="w-full" />
              </div>
            </div>

          </div>


          <!-- 右カラム -->
          <div class="space-y-4">

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
          </div>  
        </div>
        <!-- PDF アップロード 2点 -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold">必要書類アップロード</h3>

          <!-- 履歴事項全部証明書 -->
          <div
            @dragover.prevent
            @dragenter.prevent
            @drop.prevent="handleDrop"
            class="border-2 border-dashed border-gray-300 p-6 text-center cursor-pointer"
            @click="triggerFileSelect"
          >
            <p v-if="!form.history_certificate">
              履歴事項全部証明書（PDF）をドラッグ＆ドロップ または クリックして選択
            </p>
            <p v-else class="text-green-600 font-medium">
              選択済み: {{ form.history_certificate.name }}
            </p>

            <input
              type="file"
              class="hidden"
              ref="historyCertificateInput"
              accept="application/pdf"
              @change="handleFileSelect"
            />
          </div>

          <InputError :message="errors.history_certificate" />

          <!-- 口座振替依頼書 -->
<!--
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
-->
        </div>

        <div v-if="pdfUrl" class="mt-5">
          <h2 class="text-lg font-bold mb-2">PDFが生成されました</h2>

          <iframe :src="pdfUrl" width="100%" height="500px"></iframe>

          <a :href="pdfUrl" download class="text-blue-600 underline">
            ダウンロード
          </a>
        </div>
        <!-- 送信 -->
        <PrimaryButton class="mt-6" type="submit">
          データ送信
        </PrimaryButton>

        <button
          type="button"
          class="bg-blue-600 text-white px-4 py-2 rounded"
          @click="submitPDF"
        >
          PDF作成
        </button>

      </form>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, computed, toRef } from 'vue'
import { Link, router, useForm,usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Autocomplete from '@/Components/Autocomplete.vue'    
import axios from 'axios'
import { useZipcode } from '@/composables/useZipcode'


const page = usePage()
const bankInput = ref(null)

console.log(page.props,page.props.token) // ← ここで form が見える

const form = useForm({
  company_furigana: page.props.form?.company_furigana ?? 'クーネット',
  representative_furigana: page.props.form?.representative_furigana ?? 'クモダ',
  company_name: page.props.form?.company_name ?? '株式会社クーネット',
  representative: page.props.form?.representative ?? '雲田敏広',
  address_zip: page.props.form?.address_zip ?? '224-0021',
  address: page.props.form?.address ?? '横浜市',
  tel: page.props.form?.tel ?? '045-590-0090',
  fax: page.props.form?.fax ?? '045-590-0091',
  bank_type: page.props.form?.bank_type ?? '',
  bank_name: page.props.form?.bank_name ?? '',
  bank_code: page.props.form?.bank_code ?? '',
  branch_name: page.props.form?.branch_name ?? '',
  branch_code: page.props.form?.branch_code ?? '',  
  account_type: page.props.form?.account_type ?? '普通',
  account_no: page.props.form?.account_no ?? '1234567',
  account_kana: page.props.form?.account_kana ?? 'クーネット',
  account_name: page.props.form?.account_name ?? 'クーネット',
  staff: '',
  mobile: '',
  history_certificate: null,
});

// エラー
//const errors = page.props.errors || {}
const errors = ref({})

const validateRequired = () => {
  errors.value = {}

  const required = [
    'company_furigana',
    'representative_furigana',
    'company_name',
    'representative',
    'address_zip',
    'address',
    'tel',
    'bank_type',
    'bank_name',
    'branch_name',
    'account_type',
    'account_no',
    'account_kana',
    'account_name',
  ]

  required.forEach(key => {
    const v = form[key]
    if (!v || (typeof v === 'string' && v.trim() === '')) {
      errors.value[key] = '必須項目です'
    }
  })
  console.log(errors)

  return Object.keys(errors.value).length === 0
}




const candidates = ref([])
// 複数住所の対応
function selectCandidate(candidate) {
  if (!candidate) return
  form.address = candidate.label
  if (candidates.value) candidates.value = []
}

const historyCertificateInput = ref(null)

const triggerFileSelect = () => {
  historyCertificateInput.value.click()
}

const handleFileSelect = (e) => {
  const file = e.target.files[0]
  if (!file) return
  form.history_certificate = file
}

const handleDrop = (e) => {
  const file = e.dataTransfer.files[0]
  if (!file) return
  form.history_certificate = file
}
  

// 送信処理
const submitForm = () => {
  console.log(page.props.token)
  if (!validateRequired()) return

  const data = new FormData()

  Object.keys(form).forEach(key => {
    if (form[key] !== null && form[key] !== undefined) {
      data.append(key, form[key])
    }
  })
  console.log(data)
  Inertia.post(
    route('members.register.complete', { token: page.props.token }),
    data,
    {
      preserveScroll: true,
    }
  )
}


const bankCategories = ref([])
const selectedCategory = ref(form.bank_type) 

/* 銀行 */
const selectedBank = ref(null)

/* 支店 */
const selectedBranch = ref(null)

// mounted 時に session の値で初期化
onMounted(() => {
  if (form.bank_name) {
  }
})

onMounted(async () => {
  const res = await axios.get('/api/bank-categories')

  bankCategories.value = res.data.map(c => ({
    value: c.id,
    label: c.bank_name
  }))
})

const bankCategory = ref('')
const bankKey = ref(0)

const selectCategory = async (category) => {
    console.log(category.value)
  // カテゴリ確定
  selectedCategory.value = category.value
  form.bank_type = category.value

  selectedBank.value = null
  form.bank_name = ''
  form.bank_code = ''
  form.branch_name = ''
  form.branch_code = ''

  if (category.value === 7) {
    selectedBank.value = {
        bank_code: '9900',
        label: 'ゆうちょ銀行',
        bank_category: 7,
    }
    form.bank_code = '9900'
    form.bank_name = 'ゆうちょ銀行'

  } else {
    form.bank = null
  }

  bankKey.value += 1

}

const handleBankSelected = (item) => {
  console.log(item)
  selectedBank.value = item            // v-model にオブジェクトを入れる
  form.bank_name = item.label          // form に銀行名を反映
  form.bank_id = item.id               // form に銀行 id を反映
  form.bank_code = item.bank_code
  // 支店は必ずリセット
  selectedBranch.value = null
  form.branch_name = ''
  form.branch_code = ''
}


const handleBranchSelected = (branch) => {
  selectedBranch.value = branch

  form.branch_name = branch.label
  form.branch_code = branch.branch_code
//  console.log(branch,form.branch_name);
}

const fetchParams = computed(() => {
  console.log(selectedCategory.value ? { category: selectedCategory.value } : {})  
  return selectedCategory.value ? { category: selectedCategory.value } : {}
})

const extraParams = computed(() => {
    console.log(selectedBank.value);
  console.log(selectedBank.value ? { bank_code: selectedBank.value.bank_code } : {})

  return selectedBank.value ? { bank_code: selectedBank.value.bank_code } : {}
})

const pdfUrl = ref(null)

const submitPDF = async () => {
  console.log('click')

  if (!validateRequired()) return
  console.log('OK')
  try {
    const res = await axios.post('/members/pdfgenerate', form)
    if (res.data.url) {
      router.get(
        route('members.pdf.preview', {
          token: page.props.token,
          pdfUrl: res.data.url,
        })
      )
    }
  } catch (e) {
    console.error(e)
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

