<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  bgClass: {
    type: String,
    default: 'bg-indigo-100',
  },
  colorClass: {
    type: String,
    default: 'text-indigo-600',
  },
});
</script>

<template>
  <div class="flex justify-center mb-6">
    <div
      class="w-20 h-20 rounded-full flex items-center justify-center"
      :class="bgClass"
    >
      <svg
        :class="['w-[48px] h-[48px]', colorClass]"
        viewBox="0 0 24 24"
      >
        <!-- アーチ -->
        <path
          d="M7.5 10V7a4.5 4.5 0 019 0v3"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          stroke-linecap="round"
        />

        <!-- 本体 -->
        <rect x="5" y="10" width="14" height="10" rx="2.5" fill="currentColor" />

        <!-- 鍵穴 -->
        <circle cx="12" cy="14" r="1.2" fill="white"/>
        <rect x="11.4" y="14.8" width="1.2" height="3" rx="0.6" fill="white"/>
      </svg>
    </div>
  </div>
</template>
