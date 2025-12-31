<template>
  <div class="p-4">
    <h2 class="text-lg font-bold mb-2">銀行検索テスト</h2>

    <input
      v-model="keyword"
      type="text"
      placeholder="銀行名を入力"
      class="border p-2 mb-2 w-full"
    />
    <button @click="searchBanks" class="bg-blue-500 text-white px-4 py-2 rounded">
      検索
    </button>

    <div v-if="loading" class="mt-2">検索中...</div>

    <div v-if="error" class="mt-2 text-red-500">{{ error }}</div>

    <ul v-if="banks.length" class="mt-2 border-t pt-2">
      <li v-for="bank in banks" :key="bank.bank_code" class="py-1">
        {{ bank.bank_name }} ({{ bank.bank_code }})
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const keyword = ref('')
const banks = ref([])
const loading = ref(false)
const error = ref('')

// 無料デモキーを直接使う（テスト用）
const API_KEY = 'gsSkVJXNDk3C8m9Fba7s15ZrMQF4Z5'

const searchBanks = async () => {
  if (!keyword.value) return

  loading.value = true
  error.value = ''
  banks.value = []

  try {
    const params = new URLSearchParams({ freeword: keyword.value })
    const res = await fetch(`https://apis.bankcode-jp.com/v3/freeword/banks?${params}`, {
      headers: {
        'Authorization': `Bearer ${API_KEY}`
      }
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)

    const data = await res.json()
    banks.value = data.data || []
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* 簡単なスタイル */
</style>
