<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-gray-700">SaaS会社一覧</h1>
      <inertia-link
        href="/admin/saas/create"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        新規登録
      </inertia-link>
    </div>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">会社名</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">電話</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">有効</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="saas in saasList" :key="saas.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ saas.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ saas.phone }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="saas.is_active ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
              >
                {{ saas.is_active ? '✔' : '✘' }}
              </span>
            </td>
          </tr>
          <tr v-if="!saasList.length">
            <td colspan="3" class="px-6 py-4 text-center text-gray-500">データがありません</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { Link as InertiaLink } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
defineOptions({ layout: AdminLayout })

defineProps({
  saasList: {
    type: Array,
    required: true,
  },
})
</script>





