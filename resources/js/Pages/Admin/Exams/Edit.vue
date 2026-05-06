<template>
  <AppLayout>

    
  <div class="p-6 space-y-6">

    <!-- ===================== -->
    <!-- Exam編集 -->
    <!-- ===================== -->
    <div class="bg-white shadow rounded-lg p-4">
      <h2 class="text-lg font-semibold mb-4">Exam編集</h2>

      <div class="space-y-4">
        <div>
          <label class="text-sm text-gray-500">タイトル</label>
          <input
            v-model="examForm.title"
            type="text"
            class="w-full border rounded px-3 py-2"
          />
        </div>

        <div class="text-right">
          <button
            @click="updateExam"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          >
            更新
          </button>
        </div>
      </div>
    </div>
    <div>
    <h2 class="text-lg font-semibold mb-2">{{ t('uploaded_files') }}</h2>

    <div v-if="props.documents.length === 0" class="text-gray-500">{{ t('no_uploads') }}</div>

    <div v-else class="grid grid-cols-3 gap-4">
        <div v-for="doc in props.documents" :key="doc.id" class="border rounded p-2">
            <!-- サムネイル -->
            <img
                :src="`/storage/${doc.thumbnail_path}`"
                class="w-full h-32 object-contain my-2"
            />
            <div class="flex justify-between items-center mt-1">
              <button
                @click="openPdf(`/storage/${doc.file_path}`)"
                class="text-blue-600 hover:underline text-xs"
              >
                {{ $t('view_pdf') }}
              </button>
            </div>
        </div>
    </div>
    </div>
    <!-- ===================== -->
    <!-- Report一覧 -->
    <!-- ===================== -->
    <div class="bg-white shadow rounded-lg p-4">
      <h2 class="text-lg font-semibold mb-4">{{ t('rehabs.list') }}</h2>

      <div v-if="!reports || reports.length === 0" class="text-gray-400 text-sm">
        データがありません
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="report in reports"
          :key="report.id"
          class="flex justify-between items-center border rounded p-3 hover:bg-gray-50"
        >
          <!-- 左側 -->
          <div>
            <p class="font-medium">
              {{ report.is_detailed ? '詳細' : '通常' }} {{ report.diagnosis }}
            </p>
            <p class="text-xs text-gray-400">
              {{ new Date(report.created_at).toLocaleString() }}
            </p>
          </div>

          <!-- 右側 -->
          <div class="flex items-center gap-3">
          </div>
        </div>
      </div>
    </div>

  </div>
<div>
      <DialogModal
        :show="!!previewPdf"
        maxWidth="7xl"
        @close="previewPdf = null"
      >
        <template #title>
          {{ t('PDFpreview') }}
        </template>

        <template #content>
          <div class="w-[90vw] h-[80vh]">
            <iframe
              v-if="previewPdf"
              :src="previewPdf"
              class="w-full h-full border"
            />
          </div>
        </template>

        <template #footer>
          <SecondaryButton @click="previewPdf = null">
            {{ t('closed') }}
          </SecondaryButton>
        </template>
      </DialogModal>

      <DialogModal :show="showStatusModal" @close="closeModal">
        <template #title>
            {{ t('applications.status_change') }}
        </template>

        <template #content>
          <select
            v-model="statusForm.status"
            class="w-full border rounded px-3 py-2 mb-4"
          >
            <option
              v-for="s in statusOptions"
              :key="s.value"
              :value="s.value"
            >
              {{ s.label }}
            </option>
          </select>
          <!-- 日付入力 -->
          <input
            type="datetime-local"
            v-model="statusForm.date"
            class="w-full border rounded px-3 py-2 mb-4"
          />
        </template>
        <template #footer>
          <SecondaryButton @click="closeModal">
            <v-spacer />
            <v-btn text @click="showStatusModal = false">{{ t('cancel') }}</v-btn>
          </SecondaryButton>
          <PrimaryButton class="ms-3" @click="submitStatus">
              {{ t('actions.update') }}
          </PrimaryButton>
        </template>
      </DialogModal>

      <DialogModal :show="showUploadModal" @close="closeModal">
        <template #title>{{ t('applications.documents') }}</template>

        <template #content>
          <!-- ドラッグ＆ドロップ領域 -->
          <div
            class="mt-4 min-h-80 p-4 bg-[#e7dfc8] border-2 border-dashed border-gray-300 rounded text-center cursor-pointer hover:border-gray-500"
            @dragover.prevent
            @dragenter.prevent
            @drop.prevent="handleDrop"
            @click="fileInput.click()"
          >
            <p v-if="!file">{{ t('applications.uploads') }}</p>
            <p v-else class="text-sm text-gray-700">{{ t('selected') }} {{ file.name }}</p>

            <!-- hidden file input -->
            <input
              type="file"
              ref="fileInput"
              class="hidden"
              accept="image/png"
              @change="onFileChange"
            />
          </div>
        </template>

        <template #footer>
          <div class="flex justify-end gap-3">
            <SecondaryButton @click="closeModal">
              {{ t('cancel') }}
            </SecondaryButton>

            <PrimaryButton @click="submitUpload">
              {{ t('upload') }}
            </PrimaryButton>
          </div>
        </template>
      </DialogModal>      
    </div>  
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import DialogModal from '@/Components/DialogModal.vue';

import { usePage, useForm } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentPlusIcon} from '@heroicons/vue/24/outline'

const { t } = useI18n()

const page = usePage()

const props = defineProps({
  form: Object,
  reports: Array,
  documents: Array,
})

console.log(props.reports)

// Exam更新用フォーム
const examForm = useForm({
  id: props.form.id,
  title: props.form.title ?? '',
})

const previewPdf = ref(null)

const openPdf = (pdfPath) => {
  console.log('PDF PATH:', pdfPath)
  if (!pdfPath) return

  // 例：フルパス化
  previewPdf.value = pdfPath

  // 例：ここで loading true
}

const showStatusModal = ref(false)

const statusForm = ref({
  application_id: null,
  status: '',
  date: '',
})

const statusMap = {
  '受付済': 'received',
  '作業中': 'working',
  '完了': 'completed',
}


const openStatus = async (application) => {
  statusForm.value.application_id = application.id
  statusForm.value.status = statusMap[application.status] || ''

  if (application.working_at) {
    statusForm.value.date = application.working_at
  } else if (application.completed_at) {
    statusForm.value.date = application.completed_at
  } else {
    statusForm.value.date = application.created_at
  }
  //statusForm.value.date = dayjs.tz(statusForm.value.date,'Asia/Tokyo').format('YYYY-MM-DDTHH:mm')
  showStatusModal.value = true
}

const submitStatus = async () => {

  const url = `/applications/${statusForm.value.application_id}/status`

  await axios.put(
    url,
    { 
      status: statusForm.value.status,
      date: statusForm.value.date || new Date().toISOString().slice(0, 10),
    }
  )

  showStatusModal.value = false
  router.reload({ only: ['applications'] })
}

const closeModal = () => {
  showStatusModal.value = false;
  showUploadModal.value = false
}
</script>