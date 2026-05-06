<script setup>
import { computed } from 'vue'

const props = defineProps({
  tabs: {
    type: Array,
    required: true
  },
  modelValue: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

// 👉 タブクリック
const selectTab = (id) => {
  emit('update:modelValue', id)
}

// 👉 アクティブ判定（可読性UP）
const isActive = (id) => props.modelValue === id
</script>

<template>
  <div>
    <!-- タブヘッダー -->
    <div class="flex border-b border-gray-200 overflow-x-auto">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="selectTab(tab.id)"
        :class="[
          'whitespace-nowrap px-4 py-2 text-sm font-medium transition-colors border-b-2 focus:outline-none',
          isActive(tab.id)
            ? 'border-indigo-500 text-indigo-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- コンテンツ -->
    <div class="mt-4">
      <slot :active-tab="modelValue" />
    </div>
  </div>
</template>
