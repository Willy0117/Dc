<template>
  <div>
    <h1 class="text-xl font-bold mb-4">{{ t('self_case_reports') }}</h1>
    <CaseReportForm
      v-for="i in 10"
      :key="i"
      :number="i"
      v-model="caseReports[i - 1]"
    />
    <button @click="submitAll" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
      {{ t('submit') }}
    </button>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import CaseReportForm from './CaseReportForm.vue'

const { t } = useI18n()

const caseReports = reactive(
  Array.from({ length: 10 }, () => ({
    facility_name: '',
    age: '',
    gender: '',
    diagnosis: '',
    present_illness: '',
    treatment_course: '',
    past_history: '',
    rehabilitation_goal_program: '',
    future_tasks_policy: '',
    attachments: []
  }))
)

const submitAll = () => {
  console.log('Submitting all reports:', caseReports)
  // ここで Inertia.post() でサーバーに送信可能
}
</script>
