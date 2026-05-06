<script setup>
import { ref } from 'vue'

const notices = ref([
  {
    id: 1,
    title: 'システムメンテナンスのお知らせ',
    body: '2026/05/01 02:00〜04:00にメンテナンスを行います。',
    date: '2026-04-24',
    important: true,
    published: true
  },
  {
    id: 2,
    title: '新機能リリース',
    body: '会員情報のCSV出力機能を追加しました。',
    date: '2026-04-20',
    important: false,
    published: true
  },
  {
    id: 3,
    title: '重要：ログイン仕様変更',
    body: 'セキュリティ強化のためログイン方法が変更されました。',
    date: '2026-04-15',
    important: true,
    published: false
  }
])

const editNotice = (id) => {
  alert(`編集: ${id}`)
}

const deleteNotice = (id) => {
  if (confirm('削除しますか？')) {
    notices.value = notices.value.filter(n => n.id !== id)
  }
}
</script>

<template>
  <div class="bg-white rounded-xl shadow p-4">
    <!-- ヘッダー -->
    <div class="flex justify-between items-center mb-3">
      <h2 class="text-lg font-bold">お知らせ管理</h2>
      <button class="bg-blue-600 text-white px-3 py-1 rounded">
        ＋ 新規作成
      </button>
    </div>

    <!-- 一覧 -->
    <ul class="space-y-3">
      <li
        v-for="notice in notices"
        :key="notice.id"
        class="border p-3 rounded hover:bg-gray-50"
      >
        <div class="flex justify-between items-start">
          <div>
            <div class="text-sm text-gray-500">
              {{ notice.date }}
            </div>

            <div class="font-semibold">
              {{ notice.title }}
              <span v-if="notice.important" class="text-red-500 text-xs ml-2">
                重要
              </span>
            </div>

            <div class="text-sm text-gray-700 mt-1">
              {{ notice.body }}
            </div>

            <div class="text-xs mt-1">
              <span
                :class="notice.published ? 'text-green-600' : 'text-gray-400'"
              >
                {{ notice.published ? '公開中' : '非公開' }}
              </span>
            </div>
          </div>

          <!-- 操作 -->
          <div class="flex space-x-2 text-sm">
            <button
              @click="editNotice(notice.id)"
              class="text-blue-600"
            >
              編集
            </button>
            <button
              @click="deleteNotice(notice.id)"
              class="text-red-500"
            >
              削除
            </button>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>