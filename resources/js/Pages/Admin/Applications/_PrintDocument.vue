<template>
  <AppLayout>
    <template #header>{{ t('applications.document') }}</template>

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

        <!-- 印刷ボタン -->
        <div class="mt-4">
        <button
            class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 disabled:opacity-50 flex items-center space-x-1"
            :disabled="!selectedId"
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

console.log(props.document)

// 選択中のドキュメントID
const selectedId = ref(props.documents.length === 1 ? props.documents[0].id : null)

// 印刷処理
const printSelected = () => {

  if (!selectedId.value) return

  const selectedDoc = props.documents.find(d => d.id === selectedId.value)
  if (!selectedDoc) return

  const img = new Image()
  img.src = selectedDoc.file_url

  img.onload = () => {

    const w = img.naturalWidth
    const h = img.naturalHeight

    let pageSize = 'auto'

    // 代表的サイズ判定
    if (w >= 3400 && h >= 4900) {
      pageSize = 'A3'
    } else if (w >= 2400 && h >= 3400) {
      pageSize = 'A4'
    } else if (w >= 1500 && h >= 2100) {
      pageSize = '2L'
    } else {
      pageSize = 'L'
    }

    const printWindow = window.open('', '_blank')
    if (!printWindow) return

    printWindow.document.write(`
      <html>
      <head>
        <title>印刷</title>
        <style>

          @page {
            size: ${pageSize};
            margin: 0;
          }

          body{
            margin:0;
            padding:0;
            text-align:center;
          }

          img{
            width:auto;
            height:auto;
            display:block;
            margin:0 auto;
          }

        </style>
      </head>
      <body>
        <img id="print-img" src="${selectedDoc.file_url}">
      </body>
      </html>
    `)

    printWindow.document.close()

    printWindow.onload = () => {
      const imgEl = printWindow.document.getElementById('print-img')
      if (imgEl.complete) {
        printWindow.print()
      } else {
        imgEl.onload = () => printWindow.print()
      }
    }

  }
}
</script>

<style scoped>
/* 選択中のカードを目立たせる */
.border-purple-500 {
  border-width: 2px !important;
}
</style>