<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">

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
    <StepIndicator :current-step="4" />

    <!-- Content -->
    <div class="flex-1 flex items-center justify-center px-6 py-16">
      <div class="max-w-lg w-full text-center space-y-6">

        <!-- アイコン -->
        <div class="flex justify-center">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
            <CheckCircle2 class="w-10 h-10 text-green-500" />
          </div>
        </div>

        <!-- メッセージ -->
        <div class="space-y-2">
          <h1 class="text-2xl font-bold text-gray-900">申込を受け付けました</h1>
          <p class="text-gray-500 text-sm leading-relaxed">
            動注治療ライセンスのお申込みありがとうございます。<br />
            ご登録のメールアドレスに確認メールをお送りしました。<br />
            電子署名の手続きをメールよりお願いいたします。
          </p>
        </div>

        <!-- 注意事項 -->
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 text-left space-y-2">
          <p class="text-xs font-bold text-blue-700 flex items-center gap-1.5">
            <Info class="w-3.5 h-3.5" />今後の流れ
          </p>
          <ol class="text-xs text-blue-700 space-y-1.5 pl-4 list-decimal">
            <li>ご登録のメールアドレスにクラウドサインから署名依頼メールが届きます。</li>
            <li>メール内のリンクから契約書の内容をご確認いただき、電子署名をお願いします。</li>
            <li>請求書またはお支払いのご案内をメールにて送りました。お支払いをお願いいたします。</li>
            <li>署名完了及びお支払い完了後、ライセンスをご利用いただけます。</li>
          </ol>
        </div>

        <!-- お問い合わせ -->
        <p class="text-xs text-gray-400">
          ご不明な点がございましたら<br>
          {{ companyName }}<br><br>
          <a href="mailto:license_okunoclinic@alivio-japan.com" class="text-blue-600 underline">license_okunoclinic@alivio-japan.com</a>
          までお問い合わせください。
        </p>

      </div>
    </div>
    <ApplicationFooter />

  </div>
</template>

<script setup>
import { CheckCircle2, Info } from 'lucide-vue-next'
import ApplicationFooter from '@/Components/ApplicationFooter.vue'
import StepIndicator from '@/Components/StepIndicator.vue'

defineProps({
  companyName: {
    type: String,
    default: '',
  },
})

</script>