<script setup>
const props = defineProps({
  current: {
    type: String,
    required: true,
  },
})

const steps = [
  { key: 'agree', label: '規約同意' },
  { key: 'company', label: '会社情報' },
  { key: 'bank', label: '口座振替' },
  { key: 'store', label: '登録' },
  { key: 'complete', label: '完了' },
]

const currentIndex = steps.findIndex(s => s.key === props.current)

const status = index => {
  if (index < currentIndex) return 'done'
  if (index === currentIndex) return 'current'
  return 'todo'
}
</script>

<template>
  <!-- ===== PC表示 ===== -->
  <div class="hidden md:block mb-8">
    <ol class="flex items-center w-full">
      <li
        v-for="(step, index) in steps"
        :key="step.key"
        class="flex items-center w-full"
      >
        <div
          class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold"
          :class="{
            'bg-green-600 text-white': status(index) === 'done',
            'bg-blue-600 text-white': status(index) === 'current',
            'bg-gray-200 text-gray-600': status(index) === 'todo',
          }"
        >
          <span v-if="status(index) === 'done'">✓</span>
          <span v-else>{{ index + 1 }}</span>
        </div>

        <span
          class="ml-2 text-sm whitespace-nowrap"
          :class="{
            'font-bold text-blue-600': status(index) === 'current',
            'text-gray-500': status(index) === 'todo',
          }"
        >
          {{ step.label }}
        </span>

        <div
          v-if="index !== steps.length - 1"
          class="flex-1 h-0.5 mx-4"
          :class="{
            'bg-green-600': status(index) === 'done',
            'bg-gray-300': status(index) !== 'done',
          }"
        />
      </li>
    </ol>
  </div>

  <!-- ===== スマホ表示 ===== -->
  <div class="md:hidden mb-6">
    <!-- 進捗 -->
    <div class="mb-4">
      <p class="text-sm text-gray-500">
        ステップ {{ currentIndex + 1 }} / {{ steps.length }}
      </p>
      <p class="text-lg font-bold">
        {{ steps[currentIndex]?.label }}
      </p>
    </div>

    <!-- 縦ステップ -->
    <ul class="space-y-3">
      <li
        v-for="(step, index) in steps"
        :key="step.key"
        class="flex items-center"
      >
        <div
          class="w-3 h-3 rounded-full mr-3"
          :class="{
            'bg-green-600': status(index) === 'done',
            'bg-blue-600': status(index) === 'current',
            'bg-gray-300': status(index) === 'todo',
          }"
        />
        <span
          :class="{
            'font-bold text-blue-600': status(index) === 'current',
            'text-gray-500': status(index) === 'todo',
          }"
        >
          {{ step.label }}
        </span>
      </li>
    </ul>
  </div>
</template>
