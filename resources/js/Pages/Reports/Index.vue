<template>
  <AppLayout>
    <template #header>{{ t('exams.reports_list') }}</template>

    <div class="p-6">
      <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('rehabs.is_detailed') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('rehabs.facility_name') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('rehabs.age') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('rehabs.gender') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('rehabs.visit_type') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('rehabs.diagnosis') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('actions.action') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="report in props.reports.data" :key="id" class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
              <td class="px-4 py-2 text-sm">
                <span :class="report.is_detailed ? 'text-red-600' : 'text-gray-600'">
                  {{ report.is_detailed ? t('rehabs.detailed_case_(form5)') : t('rehabs.regular_case') }}
                </span>
              </td>
              <td class="px-4 py-2 text-sm">{{ report?.facility_name }}</td>
              <td class="px-4 py-2 text-sm">{{ report?.age }}</td>
              <td class="px-4 py-2 text-sm">{{ report?.gender }}</td>
              <td class="px-4 py-2 text-sm">{{ report?.visit_type }}</td>
              <td class="px-4 py-2 text-sm">{{ report?.diagnosis }}</td>
              <td class="px-3 py-2 text-center flex justify-center space-x-1">
                <Link :href="route('reports.edit', { report: report.id})" class="text-blue-500 hover:text-blue-700">
                  <PencilIcon class="w-4 h-4"/>
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
// Heroicons
import { PencilIcon, DocumentIcon, CurrencyDollarIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/solid'


const { t } = useI18n()

const props = defineProps({
  reports: Object,
})

console.log(props.reports.data)

</script>
<style>
.input-field {
  @apply w-full rounded-md border border-gray-300 px-3 py-2 text-sm
         shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500;
}
</style>

