<template>
  <div class="flex justify-between items-center mt-4">
    <!-- 左端: 先頭へ + Previous + ページ番号 + Next + 最終へ -->
    <div class="flex space-x-1 items-center">
      <button
        :disabled="paginator.current_page === 1"
        @click="changePage(1)"
        title="先頭ページへ"
        class="px-2 py-1 border rounded hover:bg-gray-200 disabled:opacity-50"
      >
        «
      </button>

      <button
        :disabled="paginator.current_page === 1"
        @click="changePage(paginator.current_page - 1)"
        title="前のページへ"
        class="px-2 py-1 border rounded hover:bg-gray-200 disabled:opacity-50"
      >
        ‹
      </button>

      <template v-for="page in displayPages" :key="page.key">
        <span
          v-if="page.type === 'page'"
          @click="changePage(page.number)"
          :class="[
            'px-3 py-1 border rounded hover:bg-gray-200 cursor-pointer',
            page.number === paginator.current_page ? 'bg-gray-300 font-bold' : ''
          ]"
        >
          {{ page.number }}
        </span>
        <span v-else class="px-2">…</span>
      </template>

      <button
        :disabled="paginator.current_page === paginator.last_page"
        @click="changePage(paginator.current_page + 1)"
        title="次のページへ"
        class="px-2 py-1 border rounded hover:bg-gray-200 disabled:opacity-50"
      >
        ›
      </button>

      <button
        :disabled="paginator.current_page === paginator.last_page"
        @click="changePage(paginator.last_page)"
        title="最終ページへ"
        class="px-2 py-1 border rounded hover:bg-gray-200 disabled:opacity-50"
      >
        »
      </button>
    </div>

    <!-- 右端: 表示件数 -->
    <div class="text-gray-600">
      {{ startItem }}-{{ endItem }} / {{ paginator.total }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  paginator: Object,          // Inertia のページネーションオブジェクト
  onPageChange: Function      // ページ番号クリック時のコールバック
})

// 表示件数計算
const startItem = computed(() => props.paginator.per_page * (props.paginator.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.paginator.per_page * props.paginator.current_page, props.paginator.total))

// ページ番号の省略表示ロジック
const displayPages = computed(() => {
  const total = props.paginator.last_page
  const current = props.paginator.current_page
  const pages = []

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push({ type: 'page', number: i, key: i })
  } else {
    pages.push({ type: 'page', number: 1, key: 1 })

    if (current > 4) pages.push({ type: 'ellipsis', key: 'start-ellipsis' })

    for (let i = Math.max(2, current - 2); i <= Math.min(total - 1, current + 2); i++) {
      pages.push({ type: 'page', number: i, key: i })
    }

    if (current < total - 3) pages.push({ type: 'ellipsis', key: 'end-ellipsis' })

    pages.push({ type: 'page', number: total, key: total })
  }

  return pages
})

// ページ切り替え
function changePage(page) {
  props.onPageChange(page)
}
</script>