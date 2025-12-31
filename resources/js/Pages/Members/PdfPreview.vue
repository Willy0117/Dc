<template>
  <GuestLayout>
    <Head title="PDF確認" />

    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
      <h2 class="text-xl font-bold mb-4">口座振替申請書 確認</h2>

      <canvas ref="canvas" class="border w-full mb-4"></canvas>

      <div class="flex gap-4">
        <a
          :href="pdfUrl"
          download
          class="bg-blue-600 text-white px-4 py-2 rounded"
        >
          ダウンロード
        </a>

        <button
          @click="goBack"
          class="bg-gray-300 px-4 py-2 rounded"
        >
          戻る
        </button>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const canvas = ref(null)
const page = usePage()
const pdfUrl = usePage().props.pdfUrl

onMounted(async () => {
  // PDF.js CDN
  const pdfjsLib = window.pdfjsLib

  pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'

  const pdf = await pdfjsLib.getDocument({
    url: pdfUrl,
    cMapUrl: '/cmaps/',
    cMapPacked: true,
  }).promise

  const page1 = await pdf.getPage(1)

  const viewport = page1.getViewport({ scale: 1.5 })
  const context = canvas.value.getContext('2d')

  canvas.value.height = viewport.height
  canvas.value.width = viewport.width

  await page1.render({
    canvasContext: context,
    viewport,
    renderInteractiveForms: true,
  }).promise
})

const goBack = () => {
  router.get(route('members.pdfcreate'))
}
</script>
