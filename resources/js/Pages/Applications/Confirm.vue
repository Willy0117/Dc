<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="max-w-3xl mx-auto flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white text-xs font-bold">DC</span>
        </div>
        <span class="font-bold text-gray-900">動注治療ライセンス申込</span>
      </div>
    </header>

    <!-- Step indicator -->
    <StepIndicator :current-step="2" />

    <div class="max-w-3xl mx-auto px-6 py-8 space-y-6">

      <div>
        <h1 class="text-2xl font-bold text-gray-900">申込内容の確認</h1>
        <p class="mt-1 text-sm text-gray-500">以下の内容をご確認ください。修正がある場合は「戻る」をクリックしてください。</p>
      </div>

      <!-- 病院情報 -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
          <Building2 class="w-4 h-4 text-blue-500" />
          <h2 class="text-sm font-bold text-gray-700">病院情報</h2>
        </div>
        <dl class="divide-y divide-gray-100">
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">法人名</dt>
            <dd class="col-span-2 text-sm font-medium text-gray-900">{{ data.corp_name }}</dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">病院名</dt>
            <dd class="col-span-2 text-sm font-medium text-gray-900">{{ data.clinic_name }}</dd>
          </div>
          <!-- 代表者情報 -->
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">代表者役職</dt>
            <dd class="col-span-2 text-sm font-medium text-gray-900">{{ data.rep_position }}</dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">代表者氏名</dt>
            <dd class="col-span-2 text-sm font-medium text-gray-900">{{ data.rep_last_name }} {{ data.rep_first_name }}</dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">住所</dt>
            <dd class="col-span-2 text-sm text-gray-900">
              〒{{ data.postal_code }}<br />
              {{ data.address1 }}{{ data.address2 }}{{ data.address3 }}
            </dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">電話番号</dt>
            <dd class="col-span-2 text-sm text-gray-900">{{ data.tel }}</dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">メールアドレス</dt>
            <dd class="col-span-2 text-sm text-gray-900">{{ data.email }}</dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">支払い方法</dt>
            <dd class="col-span-2 text-sm text-gray-900">
              {{ data.payment_method === 1 ? '銀行振込' : data.payment_method === 2 ? 'クレジットカード' : '-' }}
            </dd>
          </div>
        </dl>
      </div>

      <!-- 契約窓口 -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <User class="w-4 h-4 text-green-500" />
            <h2 class="text-sm font-bold text-gray-700">契約窓口（郵送先）</h2>
          </div>
          <span v-if="data.same_as_clinic" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">病院情報と同じ</span>
        </div>
        <dl v-if="!data.same_as_clinic" class="divide-y divide-gray-100">
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">担当者名</dt>
            <dd class="col-span-2 text-sm font-medium text-gray-900">{{ data.contact_name }}</dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">住所</dt>
            <dd class="col-span-2 text-sm text-gray-900">
              〒{{ data.contact_postal_code }}<br />
              {{ data.contact_address1 }}{{ data.contact_address2 }}{{ data.contact_address3 }}
            </dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">電話番号</dt>
            <dd class="col-span-2 text-sm text-gray-900">{{ data.contact_tel }}</dd>
          </div>
          <div class="px-6 py-3 grid grid-cols-3 gap-4">
            <dt class="text-xs text-gray-500 pt-0.5">メールアドレス</dt>
            <dd class="col-span-2 text-sm text-gray-900">{{ data.contact_email }}</dd>
          </div>
        </dl>
        <div v-else class="px-6 py-4 text-sm text-gray-500">
          病院情報の住所・連絡先を郵送先として使用します。
        </div>
      </div>

      <!-- ライセンス情報 -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Award class="w-4 h-4 text-orange-500" />
            <h2 class="text-sm font-bold text-gray-700">ライセンス対象者</h2>
          </div>
          <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-semibold">
            {{ data.licenses.length }}名
          </span>
        </div>
        <div class="divide-y divide-gray-100">
          <div
            v-for="(license, index) in data.licenses"
            :key="index"
            class="px-6 py-3 flex items-center gap-4"
          >
            <span class="w-6 h-6 bg-gray-100 text-gray-500 rounded-full text-xs flex items-center justify-center font-bold flex-shrink-0">
              {{ index + 1 }}
            </span>
            <div>
              <p class="text-sm font-medium text-gray-900">
                {{ license.last_name }} {{ license.first_name }}
                <span v-if="index === 0" class="ml-1 text-xs text-blue-500">（代表者）</span>
              </p>
              <p v-if="license.position" class="text-xs text-gray-400">{{ license.position }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 料金サマリー -->
      <div class="bg-blue-50 rounded-xl border border-blue-100 p-6">
        <h2 class="text-sm font-bold text-blue-700 mb-4 flex items-center gap-2">
          <CreditCard class="w-4 h-4" />年間ライセンス料
        </h2>
        <dl class="space-y-2 text-sm">
          <div class="flex justify-between">
            <dt class="text-gray-600">基本料金（3名まで）</dt>
            <dd class="font-medium">¥{{ data.base.toLocaleString() }}</dd>
          </div>
          <div v-if="data.extra > 0" class="flex justify-between">
            <dt class="text-gray-600">追加ライセンス（{{ data.licenses.length - 3 }}名 × 10,000円）</dt>
            <dd class="font-medium">¥{{ data.extra.toLocaleString() }}</dd>
          </div>
          <div class="flex justify-between border-t border-blue-200 pt-2">
            <dt class="text-gray-600">小計（税抜）</dt>
            <dd class="font-medium">¥{{ data.subtotal.toLocaleString() }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-600">消費税（10%）</dt>
            <dd class="font-medium">¥{{ data.tax.toLocaleString() }}</dd>
          </div>
          <div class="flex justify-between border-t border-blue-200 pt-2">
            <dt class="font-bold text-blue-800">合計（税込）</dt>
            <dd class="text-xl font-bold text-blue-700">¥{{ data.total.toLocaleString() }}</dd>
          </div>
        </dl>
      </div>

      <!-- ボタン -->
      <div class="flex justify-between">
        <Link
          :href="route('applications.register') + '?token=' + data.token"
          class="text-sm flex items-center gap-2 px-6 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition-colors"
        >
          <ArrowLeft class="w-4 h-4" />戻る
        </Link>
        <Link
          :href="route('applications.contract') + '?token=' + data.token"
          class="text-sm flex items-center gap-2 px-8 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors"
        >
          契約書を確認する
          <ArrowRight class="w-4 h-4" />
        </Link>
      </div>

    </div>
  </div>
  <ApplicationFooter />
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Building2, User, Award, CreditCard, ArrowLeft, ArrowRight } from 'lucide-vue-next'
import ApplicationFooter from '@/Components/ApplicationFooter.vue'
import StepIndicator from '@/Components/StepIndicator.vue'

const props = defineProps({
  data: { type: Object, required: true },
})

console.log(props.data)

</script>