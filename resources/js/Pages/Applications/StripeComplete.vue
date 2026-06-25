<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-6">
    <div class="max-w-lg w-full bg-white rounded-2xl border border-gray-200 p-10 text-center space-y-6">

      <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto">
        <CheckCircle2 class="w-9 h-9 text-green-500" />
      </div>

      <div class="space-y-2">
        <h1 class="text-xl font-bold text-gray-900">お支払いが完了しました</h1>
        <p class="text-sm text-gray-500 leading-relaxed">
          動注治療ライセンスのご決済ありがとうございます。<br />
          お支払い内容の確認が取れ次第、担当者よりご連絡いたします。
        </p>
      </div>

      <div class="bg-gray-50 rounded-xl p-5 text-left space-y-2 text-sm text-gray-600">
        <p class="font-semibold text-gray-700">今後の流れ</p>
        <ol class="list-decimal pl-4 space-y-1">
          <li>クラウドサインの電子署名手続きが未完了の場合は、メール内のリンクからお手続きください。</li>
          <li>署名完了及びお支払い確認後、ライセンスをご利用いただけます。</li>
        </ol>
      </div>

      <p class="text-xs text-gray-400">
        ご不明な点がございましたら<br><br>
        {{ companyName }}<br>
        <a href="mailto:license_okunoclinic@alivio-japan.com" class="text-blue-600 underline">license_okunoclinic@alivio-japan.com</a>
        までお問い合わせください。
      </p>

    </div>
  </div>
</template>

<script setup>
import { CheckCircle2 } from 'lucide-vue-next'

defineProps({
  companyName: {
    type: String,
    default: '',
  },
})
</script>