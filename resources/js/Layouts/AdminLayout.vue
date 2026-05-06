<!-- resources/js/Layouts/AdminLayout.vue -->
<template>
  <div class="min-h-screen bg-gray-100 text-gray-900">
    <!-- ヘッダー -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-lg font-semibold">Admin Panel</h1>
        <nav class="space-x-6 text-sm">
          <Link href="/admin/saas" class="text-gray-600 hover:text-blue-600">SaaS一覧</Link>
          <Link href="/dashboard" class="text-gray-600 hover:text-blue-600">ダッシュボード</Link>
        </nav>
      </div>
    </header>

    <!-- メイン -->
    <main class="max-w-7xl mx-auto px-6 py-6">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

