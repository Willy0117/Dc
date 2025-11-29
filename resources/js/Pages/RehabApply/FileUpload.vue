<template>
  <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ t('attachments') }}
            </h2>
        </template>

        <div class="py-12">

        <ExamFilesUpload label="Recommendation" v-model="files.recommendation_pdf" />
        <ExamFilesUpload label="Clinical Report 1" v-model="files.clinical_report_1" />
        <ExamFilesUpload label="Clinical Report 2" v-model="files.clinical_report_2" />

        <div class="flex justify-end">
        <button
            :disabled="!canSubmit"
            @click="submitFiles"
            class="btn-primary"
        >
            {{ t('submit_application') }}
        </button>
        </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ExamFilesUpload from './Components/ExamFilesUpload.vue'

const props = usePage().props
const applications = ref(props.applications)

const { t } = useI18n()

const files = reactive({
  recommendation_pdf: null,
  clinical_report_1: null,
  clinical_report_2: null
})

const canSubmit = computed(() => {
  return files.recommendation_pdf && files.clinical_report_1 && files.clinical_report_2
})

const submitFiles = () => {
  const uploadFile = (key, file) => {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('pdf_type', key)
    Inertia.post(route('rehab.files.upload'), formData)
  }

  Object.entries(files).forEach(([key, file]) => {
    if (file) uploadFile(key, file)
  })
}
</script>
