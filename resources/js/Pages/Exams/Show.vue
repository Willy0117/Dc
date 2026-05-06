<template>
  <AppLayout>
    <template #header>
      {{ t('exam_application_details') }}
    </template>

    <div class="p-6 space-y-6">
      <!-- 申請者情報 -->
      <section class="bg-white shadow rounded p-4 space-y-2">
        <h2 class="text-lg font-semibold">{{ t('applicant_info') }}</h2>
        <p><strong>{{ t('name') }}:</strong> {{ application.name }}</p>
        <p><strong>{{ t('birthdate') }}:</strong> {{ application.birthdate }}</p>
        <p><strong>{{ t('gender') }}:</strong> {{ application.gender }}</p>
        <p><strong>{{ t('occupation') }}:</strong> {{ application.occupation }}</p>
        <p><strong>{{ t('workplace') }}:</strong> {{ application.workplace }}</p>
        <p><strong>{{ t('department') }}:</strong> {{ application.department }}</p>
        <p><strong>{{ t('position') }}:</strong> {{ application.position }}</p>
        <p><strong>{{ t('email') }}:</strong> {{ application.email }}</p>
        <p><strong>{{ t('phone') }}:</strong> {{ application.phone }}</p>
      </section>

      <!-- 添付ファイル -->
      <section class="bg-white shadow rounded p-4 space-y-2">
        <h2 class="text-lg font-semibold">{{ t('attachments') }}</h2>
        <ul class="list-disc pl-5 space-y-1">
          <li>
            <a href="#" class="text-blue-600 underline">{{ t('certificate') }} (PDF)</a>
          </li>
          <li>
            <a href="#" class="text-blue-600 underline">{{ t('recommendation_1') }} (PDF)</a>
          </li>
          <li>
            <a href="#" class="text-blue-600 underline">{{ t('recommendation_2') }} (PDF)</a>
          </li>
        </ul>
      </section>

      <!-- 自験例1-10 -->
      <section class="bg-white shadow rounded p-4 space-y-4">
        <h2 class="text-lg font-semibold">{{ t('self_reports') }}</h2>
        <div v-for="i in 10" :key="i" class="border rounded p-3">
          <h3 class="font-semibold">{{ t('self_report') }} {{ i }}</h3>
          <p><strong>{{ t('facility') }}:</strong> {{ application[`self_report_${i}`]?.facility || '-' }}</p>
          <p><strong>{{ t('age') }}:</strong> {{ application[`self_report_${i}`]?.age || '-' }}</p>
          <p><strong>{{ t('gender') }}:</strong> {{ application[`self_report_${i}`]?.gender || '-' }}</p>
          <p><strong>{{ t('diagnosis') }}:</strong> {{ application[`self_report_${i}`]?.diagnosis || '-' }}</p>
          <p><strong>{{ t('current_history') }}:</strong> {{ application[`self_report_${i}`]?.current_history || '-' }}</p>
          <p><strong>{{ t('past_history') }}:</strong> {{ application[`self_report_${i}`]?.past_history || '-' }}</p>
          <p><strong>{{ t('rehabilitation') }}:</strong> {{ application[`self_report_${i}`]?.rehabilitation || '-' }}</p>
          <p><strong>{{ t('future_plan') }}:</strong> {{ application[`self_report_${i}`]?.future_plan || '-' }}</p>
        </div>
      </section>

      <div class="flex justify-end">
        <button @click="back" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
          {{ t('back_to_list') }}
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useI18n } from 'vue-i18n'
import { router, usePage } from '@inertiajs/vue3'

const { props } = usePage()
const application = props.application || {}

const { t } = useI18n()

const back = () => {
  router.get(route('exam.applications.index'))
}
</script>
