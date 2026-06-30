<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import AdminNotices from './AdminNotices.vue'

import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'

const examCount = ref(0)
const pdfCount  = ref(0)

onMounted(async () => {
  const res = await axios.get('/api/dashboard/stats')
  examCount.value = res.data.examCount
  pdfCount.value  = res.data.pdfCount
})
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Dashboard
      </h2>
    </template>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    </div>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <AdminNotices />
        </div>
      </div>
    </div>
  </AppLayout>
</template>