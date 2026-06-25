<!-- Components/StepIndicator.vue -->
<script setup lang="ts">
defineProps<{
  currentStep: 1 | 2 | 3 | 4
}>()

const steps = [
  { step: 1, label: '申込情報入力' },
  { step: 2, label: '内容確認' },
  { step: 3, label: '契約書確認・署名' },
  { step: 4, label: '請求' },
]
</script>

<template>
  <div class="bg-white border-b border-gray-100">
    <div class="max-w-3xl mx-auto px-6 py-3 flex items-center gap-2 text-sm">
      <template v-for="(s, i) in steps" :key="s.step">
        <span
          class="flex items-center gap-1.5 font-semibold"
          :class="currentStep === s.step ? 'text-blue-600' : 'text-gray-400'"
        >
          <span
            class="w-5 h-5 rounded-full text-xs flex items-center justify-center"
            :class="currentStep === s.step ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400'"
          >
            {{ s.step }}
          </span>
          {{ s.label }}
        </span>
        <span v-if="i < steps.length - 1" class="text-gray-300 mx-2">›</span>
      </template>
    </div>
  </div>
</template>