<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import AdminNotices from './AdminNotices.vue'

import { Link } from '@inertiajs/vue3'

defineProps({
  recentOrders: Array, // [{ id, paid_at, customer_name, video_set_name, status }]
})

const statusLabel = {
  paid: '購入済み',
  pending: '保留',
  refunded: '返金済み',
}

function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleString('ja-JP', {
    year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit',
  })
}
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Dashboard
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- 直近の注文 -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">直近の注文</h3>
            <Link :href="route('admin.orders.index')" class="text-sm text-sky-600 hover:text-sky-800 underline">
              すべての注文を見る
            </Link>
          </div>

          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500">
              <tr>
                <th class="text-left px-6 py-2 font-medium">購入日</th>
                <th class="text-left px-6 py-2 font-medium">購入者名</th>
                <th class="text-left px-6 py-2 font-medium">動画セット</th>
                <th class="text-left px-6 py-2 font-medium">状態</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in recentOrders" :key="order.id" class="border-t border-gray-100">
                <td class="px-6 py-2">{{ formatDate(order.paid_at) }}</td>
                <td class="px-6 py-2">
                  <Link :href="route('admin.orders.show', order.id)" class="text-sky-700 hover:underline">
                    {{ order.customer_name }}
                  </Link>
                </td>
                <td class="px-6 py-2">{{ order.video_set_name }}</td>
                <td class="px-6 py-2">{{ statusLabel[order.status] ?? order.status }}</td>
              </tr>
              <tr v-if="recentOrders.length === 0">
                <td colspan="4" class="px-6 py-6 text-center text-gray-400">注文はまだありません</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>