<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold">{{ t('rehab_applications') }}</h1>

    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th>{{ t('user') }}</th>
          <th>{{ t('status') }}</th>
          <th>{{ t('uploaded_files') }}</th>
          <th>{{ t('actions') }}</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="application in applications" :key="application.id">
          <td>{{ application.user.name }}</td>
          <td>{{ t(application.status) }}</td>
          <td>
            <ul class="list-disc list-inside">
              <li>{{ application.recommendation_pdf ? t('ok') : t('missing') }}: {{ t('recommendation') }}</li>
              <li>{{ application.clinical_report_1 ? t('ok') : t('missing') }}: {{ t('clinical_report_1') }}</li>
              <li>{{ application.clinical_report_2 ? t('ok') : t('missing') }}: {{ t('clinical_report_2') }}</li>
            </ul>
          </td>
          <td class="space-x-2">
            <button 
              v-if="application.status !== 'approved'" 
              @click="approve(application.id)" 
              class="btn-success"
            >
              {{ t('approve') }}
            </button>
            <button 
              v-if="application.status !== 'rejected'" 
              @click="openRejectModal(application)" 
              class="btn-danger"
            >
              {{ t('reject') }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- 差し戻しモーダル -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded shadow w-96">
        <h2 class="text-lg font-bold mb-4">{{ t('reject_application') }}</h2>
        <textarea v-model="rejectMessage" rows="4" class="w-full border p-2 mb-4"></textarea>
        <div class="flex justify-end space-x-2">
          <button @click="submitReject" class="btn-danger">{{ t('submit') }}</button>
          <button @click="closeModal" class="btn-secondary">{{ t('cancel') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Inertia } from '@inertiajs/inertia'
import { usePage } from '@inertiajs/vue3'

const { t } = useI18n()
const page = usePage()
const applications = page.props.value.applications // Inertiaで渡された一覧データ

const showModal = ref(false)
const rejectMessage = ref('')
const targetApplication = ref(null)

const openRejectModal = (application) => {
  targetApplication.value = application
  rejectMessage.value = application.reject_message || ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  targetApplication.value = null
  rejectMessage.value = ''
}

const submitReject = () => {
  Inertia.post(route('rehab.reject', targetApplication.value.id), {
    message: rejectMessage.value
  }).then(() => closeModal())
}

const approve = (id) => {
  Inertia.post(route('rehab.approve', id))
}
</script>

<style scoped>
.btn-success { @apply bg-green-500 text-white px-3 py-1 rounded; }
.btn-danger { @apply bg-red-500 text-white px-3 py-1 rounded; }
.btn-secondary { @apply bg-gray-300 text-black px-3 py-1 rounded; }
</style>
