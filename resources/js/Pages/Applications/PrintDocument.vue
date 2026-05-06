<template>
  <AppLayout>
    <template #header>{{ t('applications.print') }}</template>

    <div class="p-4 space-y-4">
        <!-- ドキュメント一覧 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
            v-for="doc in documents"
            :key="doc.id"
            class="border p-2 cursor-pointer hover:border-purple-500"
            :class="{ 'border-purple-500 bg-purple-50': selectedId === doc.id }"
            @click="selectedId = doc.id"
        >
            <img :src="doc.file_url" alt="Document" class="w-full h-auto" />
        </div>
        </div>
        <!-- 背景色 -->
        <div class="mt-6">
            <p class="mb-2">{{ t('registers.bg_color') }}</p>

            <div class="flex flex-wrap gap-4">

            <div
                v-for="color in colors"
                :key="color"
                class="cursor-pointer"
                @click="form.bg_color = color"
            >
                <img
                :src="getImageUrl(color)"
                class="!w-16 !h-16 !max-w-none rounded border-4"
                :class="form.bg_color === color
                    ? 'ring-4 ring-black scale-105'
                    : 'hover:scale-105'"
                />
            </div>

            </div>
        </div>
        <!-- 印刷ボタン -->
        <div class="mt-4">
        <button
            class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 disabled:opacity-50 flex items-center space-x-1"
            :disabled="!selectedId || printing"
            @click="printSelected"
        >
            <PrinterIcon class="w-4 h-4"/><span>{{ t('applications.print') }}</span>
        </button>
        </div>
    </div>
  </AppLayout>    
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import axios from 'axios'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PrinterIcon, PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentPlusIcon} from '@heroicons/vue/24/outline'
import { Inertia } from '@inertiajs/inertia'

const { t } = useI18n()

const props = defineProps({
  application: Object,
  documents: Array,
  filters: Object,
})

console.log(props.documents)
const printing = ref(false)
// 選択中のドキュメントID
const selectedId = ref(props.documents.length === 1 ? props.documents[0].id : null)
/* 背景色 */
const colors = ['none','green','pink','blue','orange']

const form = reactive({
  bg_color: 'none'
})

const getImageUrl = (color) => {
  return `/images/color/bg-color/${color}_thumb.png`
}


// 印刷処理
const printSelected = () => {
  if (!selectedId.value) return
  printing.value = true

  const selectedDoc = props.documents.find(d => d.id === selectedId.value)
  if (!selectedDoc) return
  console.log(selectedDoc.file_path,form.bg_color);

  // サーバー合成済み画像URL
  const url = route('composeImage', {
    file: selectedDoc.file_path,
    bg: form.bg_color || 'none'
  })

  console.log(url)

  // 既存ページに invisible iframe を作成
    const iframe = document.createElement('iframe')
    iframe.style.position = 'fixed'
    iframe.style.left = '-10000px'
    iframe.style.top = '0'
    document.body.appendChild(iframe)

    const doc = iframe.contentWindow.document
    doc.open()
    doc.write(`
    <html>
        <head>
        <title>印刷用画像</title>
        <style>
            @media print {
            @page { margin: 0; }
            body { margin: 0; }
            img { display: block; width: 100%; height: auto; }
            }
            body { margin:0; padding:0; text-align:center; }
        </style>
        </head>
        <body>
        <img id="print-image" src="${url}" alt="印刷用画像" />
        </body>
    </html>
    `)
    doc.close()

    const img = doc.getElementById('print-image')
    img.onload = () => {
            // 画像の元サイズを mm に換算
    const dpi = 300
    const widthMM = (img.naturalWidth / dpi) * 25.4
    const heightMM = (img.naturalHeight / dpi) * 25.4

    img.style.width = widthMM + 'mm'
    img.style.height = heightMM + 'mm'

    iframe.contentWindow.focus()
    iframe.contentWindow.print()
    
    setTimeout(() => {
        document.body.removeChild(iframe)
        printing.value = false
    }, 500)
    }
}
</script>

<style scoped>
/* 選択中のカードを目立たせる */
.border-purple-500 {
  border-width: 2px !important;
}
</style>