<script setup>
const notices = [
  {
    id: 1,
    title: 'Welcome Doctor',
    body: 'My Pageよりお申し込みください。',
    date: '2026-04-24',
    important: true
  },
  {
    id: 2,
    title: 'システムメンテナンスのお知らせ',
    body: '2026年5月1日 2:00〜4:00 にメンテナンスを実施します。',
    date: '2026-04-15',
    important: false
  }
]
</script>

<template>
  <div class="bg-white rounded-2xl shadow p-5">
    <h2 class="text-lg font-bold mb-4">お知らせ</h2>

    <ul class="space-y-4">
      <li
        v-for="notice in notices"
        :key="notice.id"
        class="p-3 rounded-lg hover:bg-gray-50 transition border"
      >
        <div class="flex items-center justify-between mb-1">
          <span class="text-sm text-gray-500">{{ notice.date }}</span>
          <span
            v-if="notice.important"
            class="text-xs text-red-500 font-semibold"
          >
            重要
          </span>
        </div>

        <div class="font-semibold text-gray-800">
          {{ notice.title }}
        </div>

        <div class="text-sm text-gray-600 mt-1">
          {{ notice.body }}
        </div>
      </li>
    </ul>
  </div>
</template>