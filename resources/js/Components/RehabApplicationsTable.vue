<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('submitted_at') }}</th>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('status') }}</th>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('reject_message') }}</th>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('actions') }}</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="application in applications" :key="application.id">
          <td class="px-4 py-2 text-sm text-gray-700">{{ formatDate(application.created_at) }}</td>
          <td class="px-4 py-2 text-sm text-gray-700 capitalize">{{ application.status }}</td>
          <td class="px-4 py-2 text-sm text-red-600">{{ application.reject_message || '-' }}</td>
          <td class="px-4 py-2 text-sm text-gray-700 space-x-2">
            <!-- PDFアップロードが未完了の場合 -->
            <button
              v-if="application.status === 'rejected' || application.status === 'draft'"
              @click="goToUpload(application.id)"
              class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs"
            >
              {{ t('upload_documents') }}
            </button>

            <!-- 申込済みの場合 -->
            <span v-if="application.status === 'submitted'">{{ t('submitted') }}</span>
            <span v-if="application.status === 'approved'" class="text-green-600">{{ t('approved') }}</span>
          </td>
        </tr>
        <tr v-if="applications.length === 0">
          <td colspan="4" class="px-4 py-2 text-sm text-gray-500 text-center">
            {{ t('no_applications') }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

// i18n
const { t } = useI18n()

// Inertia の props（DashboardController の戻り値）
const page = usePage()

// user と applications を取得
const user = page.props.user
const applications = ref(page.props.applications ?? []) 

const formatDate = (datetime) => {
  const d = new Date(datetime)
  return d.toLocaleDateString() + ' ' + d.toLocaleTimeString()
}

const goToUpload = (applicationId) => {
  router.get(`/rehab/upload/${applicationId}`)
}

</script>
