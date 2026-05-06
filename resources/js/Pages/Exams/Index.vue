<template>
  <AppLayout>
    <template #header>{{ t('exam_application_list') }}</template>

    <div class="p-6">
      <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ID</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('name') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('email') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('date') }}</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ t('status') }}</th>
              <th class="px-4 py-2"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="item in mockData" :key="item.id">
              <td class="px-4 py-2 text-sm">{{ item.id }}</td>
              <td class="px-4 py-2 text-sm">{{ item.name }}</td>
              <td class="px-4 py-2 text-sm">{{ item.email }}</td>
              <td class="px-4 py-2 text-sm">{{ item.date }}</td>
              <td class="px-4 py-2 text-sm">{{ item.status }}</td>
                <td class="px-4 py-2 flex space-x-2 justify-end">
                <!-- 請求書発行 -->
                <button @click="openInvoiceModal(item)"
                        class="text-indigo-600 hover:text-indigo-900">
                    <DocumentIcon class="w-5 h-5" />
                </button>

                <!-- 入金管理 -->
                <button @click="openPaymentModal(item)"
                        class="text-green-600 hover:text-green-900">
                    <CurrencyDollarIcon class="w-5 h-5" />
                </button>

                <!-- 詳細 -->
                <Link :href="route('exams.show', item.id)"
                        class="text-blue-600 hover:text-blue-900">
                    <MagnifyingGlassIcon class="w-5 h-5" />
                </Link>
                </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 請求書モーダル -->
      <div v-if="showInvoiceModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <h2 class="text-lg font-semibold mb-4">📄 {{ t('invoice_issue') }}</h2>
          <p>ID: {{ selectedItem.id }} / {{ selectedItem.name }}</p>
          <p>請求書発行モックです</p>
          <div class="mt-4 flex justify-end space-x-2">
            <button @click="showInvoiceModal = false" class="px-4 py-2 bg-gray-300 rounded">閉じる</button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded">発行</button>
          </div>
        </div>
      </div>

      <!-- 入金管理モーダル -->
      <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <h2 class="text-lg font-semibold mb-4">💰 {{ t('payment_management') }}</h2>
          <p>ID: {{ selectedItem.id }} / {{ selectedItem.name }}</p>
          <p>入金管理モックです</p>
          <div class="mt-4 flex justify-end space-x-2">
            <button @click="showPaymentModal = false" class="px-4 py-2 bg-gray-300 rounded">閉じる</button>
            <button class="px-4 py-2 bg-green-600 text-white rounded">入金済みにする</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
// Heroicons
import { DocumentIcon, CurrencyDollarIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/solid'


const { t } = useI18n()

// モックデータ
const mockData = [
  { id: 1, name: '山田太郎', email: 'taro@example.com', date: '2025-10-20', status: '未入金' },
  { id: 2, name: '佐藤花子', email: 'hanako@example.com', date: '2025-10-18', status: '入金済' },
  { id: 3, name: '田中 一郎', email: 'ichiroo@example.com', date: '2025-10-19', status: '入金済' },
  { id: 4, name: '鈴木 さくら', email: 'sakura@example.com', date: '2025-10-20', status: '入金済' },  
]

// モーダル制御
const showInvoiceModal = ref(false)
const showPaymentModal = ref(false)
const selectedItem = ref({})

const openInvoiceModal = (item) => {
  selectedItem.value = item
  showInvoiceModal.value = true
}

const openPaymentModal = (item) => {
  selectedItem.value = item
  showPaymentModal.value = true
}
</script>


