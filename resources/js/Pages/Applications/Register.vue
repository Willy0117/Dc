<template>
  <div class="min-h-screen bg-gray-50">

    <header class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="max-w-3xl mx-auto flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white text-xs font-bold">DC</span>
        </div>
        <span class="font-bold text-gray-900">動注治療ライセンス申込</span>
      </div>
    </header>

    <StepIndicator :current-step="1" />

    <div class="max-w-3xl mx-auto px-6 py-8 space-y-6">

      <div>
        <h1 class="text-2xl font-bold text-gray-900">申込情報の入力</h1>
        <p class="mt-1 text-sm text-gray-500">以下のフォームに必要事項をご入力ください。</p>
      </div>

      <!-- 病院情報 -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
          <Building2 class="w-4 h-4 text-blue-500" />病院情報
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">法人名 <span class="text-red-500">*</span></label>
            <input v-model="form.corp_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="医療法人社団○○会" />
            <p v-if="form.errors.corp_name" class="text-xs text-red-500">{{ form.errors.corp_name }}</p>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">病院名 <span class="text-red-500">*</span></label>
            <input v-model="form.clinic_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="○○クリニック" />
            <p v-if="form.errors.clinic_name" class="text-xs text-red-500">{{ form.errors.clinic_name }}</p>
          </div>
        </div>
        <!-- 代表者情報 -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">役職 <span class="text-red-500">*</span></label>
            <input v-model="form.rep_position" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="院長・理事長 等" />
            <p v-if="form.errors.rep_position" class="text-xs text-red-500">{{ form.errors.rep_position }}</p>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">姓 <span class="text-red-500">*</span></label>
            <input v-model="form.rep_last_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="山田" />
            <p v-if="form.errors.rep_last_name" class="text-xs text-red-500">{{ form.errors.rep_last_name }}</p>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">名 <span class="text-red-500">*</span></label>
            <input v-model="form.rep_first_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="太郎" />
            <p v-if="form.errors.rep_first_name" class="text-xs text-red-500">{{ form.errors.rep_first_name }}</p>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">郵便番号 <span class="text-red-500">*</span></label>
            <input v-model="form.postal_code" @input="(e) => { form.postal_code = normalizeZip(e.target.value) }" maxlength="8" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="000-0000" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">都道府県 <span class="text-red-500">*</span></label>
            <input v-model="form.address1" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="東京都" />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">市区町村 <span class="text-red-500">*</span></label>
          <input v-model="form.address2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="千代田区" />
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">番地・建物名</label>
          <input v-model="form.address3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="千代田1-1 ○○ビル101" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">電話番号 <span class="text-red-500">*</span></label>
            <input v-model="form.tel" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="03-0000-0000" />
            <p v-if="form.errors.tel" class="text-xs text-red-500">{{ form.errors.tel }}</p>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">メールアドレス <span class="text-red-500">*</span></label>
            <input type="email" v-model="form.email" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="example@example.com" />
            <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">支払い方法 <span class="text-red-500">*</span></label>
          <div class="flex gap-2">
            <button
              v-for="opt in PAYMENT_METHOD_OPTIONS"
              :key="opt.value"
              type="button"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm transition-all"
              :class="form.payment_method === opt.value ? 'border-blue-500 bg-blue-50 text-blue-800' : 'border-border bg-background text-muted-foreground hover:bg-muted'"
              @click="form.payment_method = opt.value"
            >{{ opt.label }}</button>
          </div>
        </div>
      </div>

      <!-- 契約窓口 -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
            <User class="w-4 h-4 text-green-500" />契約窓口（郵送先）
          </h2>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="sameAsClinic" @change="copyClinicAddress" class="rounded" />
            <span class="text-sm text-gray-500">病院情報と同じ</span>
          </label>
        </div>

        <div v-if="sameAsClinic" class="flex items-center gap-2 px-3 py-2.5 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-700">
          <CheckCircle2 class="w-4 h-4" />病院情報を契約窓口として使用します
        </div>

        <template v-else>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">担当者名 <span class="text-red-500">*</span></label>
            <input v-model="form.contact_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="山田 太郎" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">郵便番号</label>
              <input v-model="form.contact_postal_code" @input="(e) => { form.contact_postal_code = normalizeZip(e.target.value) }" maxlength="8" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="000-0000" />
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">都道府県</label>
              <input v-model="form.contact_address1" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="東京都" />
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">市区町村</label>
            <input v-model="form.contact_address2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="千代田区" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">番地・建物名</label>
            <input v-model="form.contact_address3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="千代田1-1 ○○ビル101" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">電話番号</label>
              <input v-model="form.contact_tel" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="03-0000-0000" />
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">メールアドレス</label>
              <input type="email" v-model="form.contact_email" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="example@example.com" />
            </div>
          </div>
        </template>
      </div>

      <!-- ライセンス情報 -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
            <Award class="w-4 h-4 text-orange-500" />ライセンス対象者
          </h2>
          <button type="button" @click="addLicense" class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
            <Plus class="w-3.5 h-3.5" />追加
          </button>
        </div>

        <!-- 料金プレビュー -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div class="text-sm text-blue-700">
            <span class="font-semibold">{{ form.licenses.length }}名</span>
            <span class="text-xs ml-1">（3名まで{{ fee.base.toLocaleString() }}円/年、4名目以降+{{ props.data.personal_fee.toLocaleString() }}円/年）</span>
          </div>
          <div class="text-left sm:text-right">
            <div class="text-lg font-bold text-blue-700">¥{{ fee.subtotal.toLocaleString() }}<span class="text-xs font-normal">（税抜）</span></div>
            <div class="text-xs text-blue-500">税込 ¥{{ fee.total.toLocaleString() }}</div>
          </div>
        </div>

      <div v-for="(license, index) in form.licenses" :key="index" class="border border-gray-100 rounded-lg p-4 bg-gray-50 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">ライセンス {{ index + 1 }}</span>
          <button v-if="form.licenses.length > 1" type="button" @click="removeLicense(index)" class="text-red-400 hover:text-red-600 p-1">
            <X class="w-3.5 h-3.5" />
          </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">役職</label>
            <input v-model="license.position" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500" :placeholder="index === 0 ? '院長・理事長 等' : '役職'" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">姓 <span class="text-red-500">*</span></label>
            <input v-model="license.last_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500" placeholder="山田" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">名 <span class="text-red-500">*</span></label>
            <input v-model="license.first_name" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500" placeholder="太郎" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">メールアドレス</label>
            <input
              type="email"
              v-model="license.email"
              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500"
              placeholder="example@example.com"
            />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">医師番号</label>
            <input
              v-model="license.doctor_number"
              @input="(e) => { license.doctor_number = normalizeDoctorNumber(e.target.value) }"
              maxlength="6"
              inputmode="numeric"
              class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500"
              placeholder="123456"
            />
          </div>
        </div>
      </div>
    </div>
      <!-- 送信ボタン -->
      <div class="flex justify-end">
        <button type="button" @click="handleSubmit" :disabled="form.processing" class="text-sm flex items-center gap-2 px-8 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 disabled:opacity-50 transition-colors">
          内容確認へ
          <ArrowRight class="w-4 h-4" />
        </button>
      </div>

    </div>
    <ApplicationFooter />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, toRef } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useZipcode } from '@/composables/useZipcode'
import { Building2, User, Award, Plus, X, ArrowRight, CheckCircle2 } from 'lucide-vue-next'
import ApplicationFooter from '@/Components/ApplicationFooter.vue'
import { PAYMENT_METHOD_OPTIONS } from '@/composables/useOrganizationForm'
import StepIndicator from '@/Components/StepIndicator.vue'

const props = defineProps<{
  data: any
}>()

const form = useForm({
  organization_id:      props.data.organization_id,
  corp_name:            props.data.corp_name            ?? '',
  clinic_name:          props.data.clinic_name          ?? '',
  postal_code:          props.data.postal_code          ?? '',
  address1:             props.data.address1             ?? '',
  address2:             props.data.address2             ?? '',
  address3:             props.data.address3             ?? '',
  tel:                  props.data.tel                  ?? '',
  email:                props.data.email                ?? '',
  payment_method:       props.data.payment_method       ?? 2,
  contact_name:         props.data.contact_name         ?? '',
  contact_postal_code:  props.data.contact_postal_code  ?? '',
  contact_address1:     props.data.contact_address1     ?? '',
  contact_address2:     props.data.contact_address2     ?? '',
  contact_address3:     props.data.contact_address3     ?? '',
  contact_tel:          props.data.contact_tel          ?? '',
  contact_email:        props.data.contact_email        ?? '',
  same_as_clinic:       props.data.same_as_clinic       ?? false,
  licenses: props.data.licenses ?? [
    { position: '', last_name: '', first_name: '', doctor_number: '', email: '' },
    { position: '', last_name: '', first_name: '', doctor_number: '', email: '' },
    { position: '', last_name: '', first_name: '', doctor_number: '', email: '' },
  ],
  corporate_fee:        props.data.corporate_fee,
  personal_fee:         props.data.personal_fee,
  // 以下は送信時に計算して上書き
  rep_position:         props.data.rep_position  ?? props.data.licenses?.[0]?.position  ?? '',
  rep_last_name:        props.data.rep_last_name  ?? props.data.licenses?.[0]?.last_name  ?? '',
  rep_first_name:       props.data.rep_first_name ?? props.data.licenses?.[0]?.first_name ?? '',
  // 以下は送信時に計算して上書き
  base:     0,
  extra:    0,
  subtotal: 0,
  tax:      0,
  total:    0,
  token: props.data.token,
  needs_agreement: props.data.needs_agreement,
})

const sameAsClinic = ref(false)

const copyClinicAddress = () => {
  if (sameAsClinic.value) {
    form.contact_postal_code = form.postal_code
    form.contact_address1    = form.address1
    form.contact_address2    = form.address2
    form.contact_address3    = form.address3
    form.contact_tel         = form.tel
    form.contact_email       = form.email
    form.same_as_clinic      = true
  } else {
    form.same_as_clinic = false
  }
}

const normalizeZip = (value: string) => {
  if (!value) return ''
  value = value.replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
  value = value.replace(/[ー－―‐]/g, '-')
  return value.replace(/[^0-9-]/g, '')
}

useZipcode(toRef(form, 'postal_code'), {
  prefecture: toRef(form, 'address1'),
  address1:   toRef(form, 'address2'),
  address2:   toRef(form, 'address3'),
})

useZipcode(toRef(form, 'contact_postal_code'), {
  prefecture: toRef(form, 'contact_address1'),
  address1:   toRef(form, 'contact_address2'),
  address2:   toRef(form, 'contact_address3'),
})

const addLicense = () => {
  form.licenses.push({ position: '', last_name: '', first_name: '', doctor_number: '', email: '' })
}

const removeLicense = (index: number) => {
  form.licenses.splice(index, 1)
}

// 料金計算（ライセンス数が変わるたびに再計算）
const fee = computed(() => {
  const count    = form.licenses.length
  const base     = props.data.corporate_fee
  const extra    = Math.max(0, count - 3) * props.data.personal_fee
  const subtotal = base + extra
  const tax      = Math.floor(subtotal * 0.1)
  const total    = subtotal + tax
  return { count, base, extra, subtotal, tax, total }
})

const handleSubmit = () => {
  form.subtotal = fee.value.subtotal
  form.tax      = fee.value.tax
  form.total    = fee.value.total
  form.base     = fee.value.base
  form.extra    = fee.value.extra
  // 医師番号を6桁ゼロ埋め（4〜6桁の入力値のみ対象）
  form.licenses = form.licenses.map(license => ({
    ...license,
    doctor_number: license.doctor_number
      ? license.doctor_number.padStart(6, '0')
      : license.doctor_number,
  }))
  form.post(route('applications.register.store'), {
    onError: (errors) => {
      console.log(errors)
    }
  })
}
const normalizeDoctorNumber = (value: string) => {
  if (!value) return ''
  // 全角数字を半角に変換
  value = value.replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
  // 数字以外を除去
  value = value.replace(/[^0-9]/g, '')
  // 6桁までに制限
  return value.slice(0, 6)
}

</script>