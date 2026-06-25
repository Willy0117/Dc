<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent
      class="w-[98vw] max-w-[98vw] max-h-[95vh] p-6 overflow-y-auto"
    >
     <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Award class="w-5 h-5 text-primary" />
          ライセンス証発行
        </DialogTitle>
      </DialogHeader>

      <!-- 組織名 -->
      <div class="bg-muted/50 rounded-lg px-4 py-2 text-sm font-medium">
        {{ org?.name }}
      </div>
      <!-- メールアドレス -->
      <div class="space-y-1.5">
        <Label>証書表示名</Label>
        <Textarea
          v-model="displayName"
          rows="2"
          placeholder="表示名を入力（改行で2行になります）"
        />
        <Label for="license-email">送信先メールアドレス</Label>
        <div v-if="feeLoading" class="text-xs text-muted-foreground flex items-center gap-1.5">
          <Loader2 class="w-3.5 h-3.5 animate-spin" />情報を取得中...
        </div>
        <Input
          v-else
          id="license-email"
          v-model="email"
          type="email"
          placeholder="example@example.com"
        />
      </div>

      <!-- PDFプレビュー -->
      <div
        id="license-pdf-container"
        class="w-full border rounded-xl p-4 bg-gray-50 overflow-auto"
        style="max-height: calc(90vh - 260px);"
      ></div>

      <div v-if="errorMessage" class="text-sm text-destructive bg-destructive/10 px-4 py-2 rounded-lg">
        {{ errorMessage }}
      </div>

      <!-- フッター -->
      <DialogFooter class="flex justify-between gap-2">
        <!-- 左端 -->
        <Button variant="outline" @click="regenerate">
          <RefreshCw class="w-4 h-4 mr-2" />
          再作成
        </Button>
        <!-- 右側 -->
        <div class="flex gap-2">
          <Button variant="outline" @click="$emit('update:open', false)">
            キャンセル
          </Button>
          <Button variant="outline" @click="printLicense">
            <Printer class="w-4 h-4 mr-2" />
            印刷
          </Button>
          <Button @click="mailLicense" :disabled="!email">
            <Mail class="w-4 h-4 mr-2" />
            メール送付
          </Button>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue'

import axios from 'axios'
import { Award, Mail, Printer, Loader2, RefreshCw } from 'lucide-vue-next'

import {
  Dialog, DialogContent, DialogHeader,
  DialogTitle, DialogFooter,
} from '@/components/ui/dialog'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Input  } from '@/components/ui/input'
import { Label  } from '@/components/ui/label'
import { toast } from 'vue-sonner'

const props = defineProps({
  open:   { type: Boolean, default: false },
  org:    { type: Object,  default: null  },
  pdfUrl: { type: String,  default: null  },
})

const displayName = ref('')

const currentPdfUrl = ref(props.pdfUrl)

const emit = defineEmits(['update:open'])

const email      = ref('')
const feeLoading = ref(false)

const fetchFee = async () => {
  if (!props.org) return
  feeLoading.value = true
  try {
    const { data } = await axios.get(route('admin.organizations.fee', props.org.id))
    email.value = data.email ?? ''
  } catch (e) {
    toast.error('メールアドレスの取得に失敗しました')
  } finally {
    feeLoading.value = false
  }
}

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
  if (!container) return
  container.innerHTML = ''

  await nextTick()

  const page = await pdf.getPage(1)
  const baseViewport = page.getViewport({ scale: 1 })

  // コンテナの実幅を取得（パディング抜き）
  const style = window.getComputedStyle(container)
  const paddingLeft = parseFloat(style.paddingLeft)
  const paddingRight = parseFloat(style.paddingRight)
  const containerWidth = container.clientWidth - paddingLeft - paddingRight

  console.log('containerWidth:', containerWidth)
  console.log('PDF baseWidth:', baseViewport.width)

  const scale = containerWidth / baseViewport.width
  console.log('scale:', scale)

  for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
    const p = await pdf.getPage(pageNum)
    const viewport = p.getViewport({ scale })

    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    canvas.width  = viewport.width
    canvas.height = viewport.height
    canvas.style.display = 'block'
    canvas.style.maxWidth = '100%'
    canvas.classList.add('shadow', 'bg-white')

    container.appendChild(canvas)

    await p.render({ canvasContext: ctx, viewport }).promise
  }
}


const printLicense = () => {
  window.print()
}

const mailLicense = async () => {
  try {
    await axios.post(route('admin.organizations.license.mail', props.org.id), {
      email: email.value,
      pdf_path: currentPdfUrl.value,
    })
    console.log('メール送付成功') 
    toast.success('メールを送付しました！')
    emit('update:open', false)
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'メール送付に失敗しました')
  }
}

watch(() => [props.open, props.pdfUrl], async ([isOpen, url]) => {
  if (isOpen && props.org) {
    fetchFee()
    displayName.value = props.org.name  // ← ここに追加するだけ
    if (url) {
      currentPdfUrl.value = url 
      await nextTick()
      await nextTick()
      await renderPdf(url, 'license-pdf-container')
    }
  }
})

const regenerate = async () => {
  if (!props.org) return

  try {
    console.log('displayName:', displayName.value)  // ← 改行が入っているか確認
    const { data } = await axios.post(route('admin.organizations.license', props.org.id), {
      display_name: displayName.value,
    })
    currentPdfUrl.value = data.url
    toast.success('再作成に成功しました！')
    await nextTick()
    await renderPdf(data.url, 'license-pdf-container')
  } catch (e) {
    toast.error(e.response?.data?.message ?? '再作成に失敗しました')
  }
}
</script>