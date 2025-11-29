<template>
  <AppLayout :title="t('upload_pdf')">
    <template #header>{{ t('upload_pdf') }}</template>

    <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-lg mt-6 space-y-4">

      <!-- カテゴリ -->
      <div>
        <label class="block mb-1">{{ t('category') }}</label>
        <select v-model="form.category" class="w-full border rounded px-3 py-2">
          <option value="academic_meeting">{{ t('academic_meeting') }}</option>
          <option value="seminar">{{ t('seminar') }}</option>
          <option value="paper">{{ t('paper') }}</option>
        </select>
      </div>

      <!-- 役割 -->
      <div>
        <label class="block mb-1">{{ t('role') }}</label>
        <select v-model="form.role" class="w-full border rounded px-3 py-2">
          <option value="participant">{{ t('participant') }}</option>
          <option value="speaker">{{ t('speaker') }}</option>
          <option value="chair">{{ t('chair') }}</option>
          <option value="author">{{ t('author') }}</option>
        </select>
      </div>

      <!-- 組織名 -->
      <div>
        <label class="block mb-1">{{ t('organization_name') }}</label>
        <input v-model="form.organization_name" type="text" class="w-full border rounded px-3 py-2" />
      </div>

      <!-- PDF ファイル -->
      <div>
        <label class="block mb-1">{{ t('file') }}</label>
        <input type="file" @change="onFileChange" accept="application/pdf" class="w-full" />
      </div>

      <!-- 保存ボタン -->
      <div class="flex justify-end">
        <button @click="submit" class="bg-blue-500 text-white px-5 py-2 rounded hover:bg-blue-600">
          {{ t('upload') }}
        </button>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const form = useForm({
  category: '',
  role: '',
  organization_name: '',
  file: null
})

const onFileChange = (e) => {
  form.file = e.target.files[0]
}

const submit = () => {
  form.post('/profile/pdf-uploads')
}
</script>

