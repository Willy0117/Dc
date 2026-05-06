<template>
  <GuestLayout>
    <RegisterStep current="bank" />
    <Head title="PDF確認" />

    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
      <h2 class="text-xl font-bold mb-4">口座振替申請書 確認</h2>

      <div
        id="pdf-container"
        class="space-y-6 overflow-y-auto max-h-[80vh] border p-4 bg-gray-50"
      ></div>

      <div class="space-y-3 mt-6">
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="confirmed" />
          <span>記載内容に相違ありません。</span>
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="downloaded" />
          <span>PDFをダウンロードしました。</span>
        </label>
      </div>

      <div class="flex gap-4 items-center">
        <PrimaryButton
          type="button"
          class="mt-6 h-10 px-4 flex items-center justify-center"
          :disabled="!canSubmit"
          @click="submitRegister"
        >
          データ登録
        </PrimaryButton>

        <a
          :href="confirmed ? pdfUrl : null"
          download
          class="mt-6 h-10 px-4 flex items-center justify-center rounded text-white"
          :class="confirmed
            ? 'bg-blue-600 cursor-pointer'
            : 'bg-gray-400 cursor-not-allowed pointer-events-none'
          "
        >
          {{ t('download') }}
        </a>

        <button
          @click="goBack"
          class="mt-6 h-10 px-4 flex items-center justify-center rounded bg-gray-300"
        >
          {{ t('revise') }}
        </button>
      </div>

    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import RegisterStep from '@/Components/RegisterStep.vue'    
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const canvas = ref(null)
const page = usePage()
const pdfUrl = usePage().props.pdfUrl

const confirmed = ref(false)
const downloaded = ref(false)

const canSubmit = computed(() => {
  return confirmed.value && downloaded.value
})

onMounted(async () => {
  const pdfjsLib = window.pdfjsLib

  pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'

  const pdf = await pdfjsLib.getDocument({
    url: pdfUrl,
    cMapUrl: '/cmaps/',
    cMapPacked: true,
  }).promise

  const container = document.getElementById('pdf-container')

  // 全ページ描画
  for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
    const page = await pdf.getPage(pageNum)

    const viewport = page.getViewport({ scale: 1.5 })
    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')

    canvas.width = viewport.width
    canvas.height = viewport.height
    canvas.classList.add('shadow', 'mx-auto')

    container.appendChild(canvas)

    await page.render({
      canvasContext: context,
      viewport,
      renderInteractiveForms: true,
    }).promise
  }
})

const submitRegister = () => {
  router.get(
    route('members.completeRegistration', {
      token: page.props.token,
    })
  )
}

const goBack = () => {
  router.get(
    route('members.register.register', { token: page.props.token }),
    {},
    {
      preserveState: true,
      preserveScroll: true,
    }
  )
}
</script>
