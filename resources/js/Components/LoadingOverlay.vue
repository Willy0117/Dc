<script setup>
import { isLoading } from '@/composables/useLoading'
</script>

<template>
<div v-if="isLoading"
     class="fixed inset-0 flex items-start justify-center pt-40 z-[9998]">

  <div class="absolute inset-0"></div>

  <div class="relative bg-white px-6 py-4 rounded-2xl shadow-xl text-center">
    <div class="animate-spin h-8 w-8 border-4 border-gray-300 border-t-blue-500 rounded-full mx-auto mb-3"></div>
    <p class="text-sm text-gray-600">処理中です...</p>
  </div>

</div>
</template>