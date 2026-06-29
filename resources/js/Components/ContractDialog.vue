<!-- resources/js/Components/ContractDialog.vue -->
<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" @click.self="$emit('update:open', false)">
      <div class="bg-gray-50 rounded-xl shadow-xl w-[90vw] max-w-4xl h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 bg-white border-b rounded-t-xl">
          <div>
            <p class="text-xs text-muted-foreground">契約書の確認</p>
            <h2 class="text-lg font-semibold">{{ org?.name }}</h2>
          </div>
          <Button variant="ghost" size="icon" @click="$emit('update:open', false)">
            <X class="w-4 h-4" />
          </Button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">

          <div v-if="loading" class="flex justify-center items-center h-40 text-muted-foreground text-sm">
            <Loader2 class="w-5 h-5 animate-spin mr-2" />読み込み中...
          </div>

          <template v-else>
            <!-- 契約書（type=2優先、なければtype=1） -->
            <div v-if="contract">
              <h3 class="text-sm font-semibold text-gray-700 mb-2">ライセンス契約書</h3>
              <div
                id="dialog-pdf-contract"
                class="space-y-6 border rounded-xl p-4 bg-white"
              />
            </div>

            <!-- 合意書（type=4優先、なければtype=3） -->
            <div v-if="agreement">
              <h3 class="text-sm font-semibold text-gray-700 mb-2">合意書</h3>
              <div
                id="dialog-pdf-agreement"
                class="space-y-6 border rounded-xl p-4 bg-white"
              />
            </div>
          </template>

        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-white border-t rounded-b-xl flex justify-end">
          <Button variant="outline" @click="$emit('update:open', false)">閉じる</Button>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { watch, nextTick, ref, computed } from 'vue'
import { X, Loader2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const props = defineProps({
  open: Boolean,
  org:  { type: Object, default: null },
})

const emit = defineEmits(['update:open'])

const loading = ref(false)

// type=2優先、なければtype=1
const contract = computed(() =>
  props.org?.documents_map?.[2] ?? props.org?.documents_map?.[1] ?? null
)

// type=4優先、なければtype=3
const agreement = computed(() =>
  props.org?.documents_map?.[4] ?? props.org?.documents_map?.[3] ?? null
)

// application.contract.vue と同実装
const renderPdf = async (url, containerId) => {
    const pdfjsLib = window.pdfjsLib

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'

    const pdf = await pdfjsLib.getDocument({
        url,
        cMapUrl: '/cmaps/',
        cMapPacked: true,
    }).promise

    const container = document.getElementById(containerId)

    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page     = await pdf.getPage(pageNum)
        const viewport = page.getViewport({ scale: 1.5 })
        const canvas   = document.createElement('canvas')
        const context  = canvas.getContext('2d')

        canvas.width  = viewport.width
        canvas.height = viewport.height
        canvas.classList.add('shadow', 'mx-auto', 'bg-white')

        container.appendChild(canvas)

        await page.render({
            canvasContext: context,
            viewport,
            renderInteractiveForms: true,
        }).promise
    }
}

watch(() => props.open, async (val) => {
  if (!val) {
    ;['dialog-pdf-contract', 'dialog-pdf-agreement'].forEach(id => {
      const el = document.getElementById(id)
      if (el) el.innerHTML = ''
    })
    return
  }

  loading.value = true
  await nextTick()
  loading.value = false  // ← 追加
  await nextTick()

  try {
    if (contract.value) {
      await renderPdf(contract.value.pdf_url, 'dialog-pdf-contract')
    }
    if (agreement.value) {
      await renderPdf(agreement.value.pdf_url, 'dialog-pdf-agreement')
    }
  } finally {
    loading.value = false
  }
})
</script>