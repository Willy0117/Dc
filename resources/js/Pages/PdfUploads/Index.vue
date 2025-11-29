<!-- resources/js/Pages/PdfUploads/Index.vue -->
<template>
  <AppLayout :title="$t('pdf_upload')">
    <template #header>{{ $t('pdf_upload') }}</template>

    <div class="max-w-3xl mx-auto py-6 space-y-6">

      <!-- PDFアップロードフォーム -->
      <div class="p-4 border rounded bg-gray-50">
        <h2 class="text-lg font-semibold mb-2">{{ $t('upload_pdf') }}</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- ファイルドラッグ&ドロップ -->
          <div
            class="border-2 border-dashed border-gray-300 p-4 rounded cursor-pointer text-center"
            @dragover.prevent
            @drop.prevent="onFileDrop"
          >
            <p>{{ $t('drag_drop_pdf') }}</p>
            <input type="file" @change="onFileChange" class="hidden" ref="fileInput" />
            <button type="button" class="btn-secondary mt-2" @click="$refs.fileInput.click()">
              {{ $t('select_file') }}
            </button>
            <p v-if="form.file">{{ form.file.name }}</p>
          </div>

          <!-- カテゴリ -->
          <div>
            <label class="block mb-1">{{ $t('category') }}</label>
            <select v-model="form.category" required class="input w-full">
              <option value="conference">{{ $t('conference') }}</option>
              <option value="seminar">{{ $t('seminar') }}</option>
              <option value="journal">{{ $t('journal') }}</option>
            </select>
          </div>

          <!-- 役割 -->
          <div>
            <label class="block mb-1">{{ $t('role') }}</label>
            <input v-model="form.role" type="text" required class="input w-full" />
          </div>

          <!-- 組織名 -->
          <div>
            <label class="block mb-1">{{ $t('organization_name') }}</label>
            <input v-model="form.organization_name" type="text" required class="input w-full" />
          </div>

          <div class="flex justify-end">
            <button type="submit" class="btn-primary">{{ $t('upload') }}</button>
          </div>
        </form>
      </div>

      <!-- アップロード一覧 -->
      <div>
        <h2 class="text-lg font-semibold mb-2">{{ $t('uploaded_files') }}</h2>

        <div v-if="uploads.length === 0" class="text-gray-500">{{ $t('no_uploads') }}</div>

        <div v-else class="grid grid-cols-3 gap-4">
          <div v-for="upload in uploads" :key="upload.id" class="border rounded p-2">
            <div class="text-sm font-medium">{{ upload.organization_name }}</div>
            <div class="text-xs text-gray-500">{{ upload.role }} - {{ upload.category }}</div>
            <!-- サムネイル -->
            <img
              v-if="upload.thumbnail_path"
              :src="`/pdf-uploads/${upload.id}/thumbnail`"
              alt="PDF Thumbnail"
              class="w-full h-32 object-contain my-2"
            />
            <div v-else class="w-full h-32 bg-gray-100 flex items-center justify-center my-2 text-gray-400 text-xs">
              {{ $t('no_thumbnail') }}
            </div>

            <div class="flex justify-between items-center mt-1">
              <a
                :href="`/pdf-uploads/${upload.id}/view`"
                target="_blank"
                class="text-blue-600 hover:underline text-xs"
              >
                {{ $t('view_pdf') }}
              </a>

              <span
                class="text-xs px-1 rounded"
                :class="{
                  'bg-yellow-200 text-yellow-800': upload.status==='pending',
                  'bg-green-200 text-green-800': upload.status==='approved',
                  'bg-red-200 text-red-800': upload.status==='rejected'
                }"
              >
                {{ $t(upload.status) }}
              </span>
            </div>

            <div v-if="upload.status==='rejected'" class="text-xs text-red-600 mt-1">
              {{ upload.rejection_message }}
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  uploads: Array
})

const form = useForm({
  file: null,
  category: '',
  role: '',
  organization_name: ''
})

const fileInput = ref(null)

function onFileChange(e) {
  form.file = e.target.files[0]
}

function onFileDrop(e) {
  const files = e.dataTransfer.files
  if(files.length > 0) form.file = files[0]
}

function submit() {
  const data = new FormData()
  data.append('file', form.file)
  data.append('category', form.category)
  data.append('role', form.role)
  data.append('organization_name', form.organization_name)

  form.post('/pdf-uploads', { data })
}
</script>



