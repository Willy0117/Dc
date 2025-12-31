<script setup>
const emit = defineEmits(['update:modelValue'])

const selectDummy = () => {
  emit('update:modelValue', { test: 'ok' })
}
</script>

<template>
  <div class="border rounded p-4 bg-gray-50">
    <div class="font-semibold mb-2">金融機関選択</div>

    <button
      type="button"
      class="px-4 py-2 bg-blue-600 text-white rounded"
      @click="selectDummy"
    >
      銀行を選択
    </button>

    <div v-if="modelValue" class="mt-3 text-sm">
      選択中：
      <pre class="bg-white p-2 mt-1">
{{ modelValue }}
      </pre>
    </div>
  </div>
</template>

