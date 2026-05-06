<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import AdminNotices from './AdminNotices.vue';
import Welcome from '@/Components/Welcome.vue';

import { ref, onMounted } from 'vue'
import axios from 'axios'
import { DocumentTextIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline'
import { Link } from '@inertiajs/vue3'


const examCount = ref(0)
const pdfCount = ref(0)

onMounted(async () => {
  const res = await axios.get('/api/dashboard/stats')

  examCount.value = res.data.examCount
  pdfCount.value = res.data.pdfCount
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

            <!--
            <Link
            :href="route('admin.exams.index')"
            class="bg-white shadow rounded-2xl p-6 flex items-center justify-between hover:shadow-lg transition"
            >
            <div>
                <p class="text-sm text-gray-500">今月の試験申請</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">
                {{ examCount }}
                </p>
            </div>
            <ClipboardDocumentCheckIcon class="w-10 h-10 text-blue-300" />
            </Link>
            -->
            <!-- PDF申請 -->
            <Link
            :href="route('admin.pdf-uploads.index')"
            class="bg-white shadow rounded-2xl p-6 flex items-center justify-between hover:shadow-lg transition"
            >
            <div>
                <p class="text-sm text-gray-500">単位取得申請 要対応数</p>
                <p class="text-3xl font-bold text-red-500 mt-2">
                {{ pdfCount }}
                </p>
                <p v-if="pdfCount > 0" class="text-xs text-red-400 mt-1">
                対応が必要です
                </p>
            </div>
            <DocumentTextIcon class="w-10 h-10 text-red-300" />
            </Link>
            <!-- PDF申請 -->
            <Link
            :href="route('admin.pdf-uploads.index')"
            class="bg-white shadow rounded-2xl p-6 flex items-center justify-between hover:shadow-lg transition"
            >
            <div>
                <p class="text-sm text-gray-500">更新申請 要対応数</p>
                <p class="text-3xl font-bold text-red-500 mt-2">
                {{ pdfCount }}
                </p>
                <p v-if="pdfCount > 0" class="text-xs text-red-400 mt-1">
                対応が必要です
                </p>
            </div>
            <DocumentTextIcon class="w-10 h-10 text-red-300" />
            </Link>

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
