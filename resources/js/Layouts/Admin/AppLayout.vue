<template>
  <div class="flex flex-col h-screen bg-white">
    <div class="flex flex-1">
      <!-- 左メニュー -->
    <Navigation class="flex flex-col h-full bg-gray-100" />

    <!-- メインエリア -->
    <div class="flex-1 flex flex-col">
      <!-- ヘッダー -->
      <header class="bg-white shadow flex items-center justify-between px-4 h-16">
        <div v-if="$slots.header" class="text-lg font-semibold">
          <slot name="header" />
        </div>

        <div class="flex items-center space-x-4 text-sm">

          <!-- Webhook通知ベル -->
          <div class="relative inline-block text-left">
            <button
              @click="toggleWebhookLogs"
              class="relative p-2 rounded-full hover:bg-gray-100 focus:outline-none"
            >
              <BellIcon class="w-6 h-6 text-gray-600" />
              <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full"
              >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
              </span>
            </button>

            <!-- Webhookログ一覧ドロップダウン -->
            <div
              v-if="webhookLogsOpen"
              class="absolute right-0 mt-2 w-96 max-h-96 overflow-y-auto rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
            >
              <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">Webhook通知</span>
                <button
                  v-if="unreadCount > 0"
                  @click="markAllRead"
                  class="text-xs text-blue-600 hover:underline"
                >すべて既読にする</button>
              </div>
              <div v-if="webhookLogs.length === 0" class="px-4 py-6 text-sm text-gray-400 text-center">
                通知はありません
              </div>
              <div
                v-for="log in webhookLogs"
                :key="log.id"
                class="px-4 py-3 border-b border-gray-50 last:border-0"
                :class="!log.is_read ? 'bg-blue-50' : ''"
              >
                <div class="flex items-center justify-between">
                  <span
                    class="text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="log.source === 'cloudsign' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700'"
                  >{{ log.source === 'cloudsign' ? 'クラウドサイン' : 'Stripe' }}</span>
                  <span class="text-xs text-gray-400">{{ formatDate(log.created_at) }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ log.event_type }}</p>
              </div>
            </div>
          </div>
          <!--
          <div class="relative inline-block text-left">
            <button
              @click="open = !open"
              class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none"
            >
              <GlobeAltIcon class="w-5 h-5 mr-2" /> {{ t('language') }}
              <svg
                class="-mr-1 ml-2 h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.186l3.71-3.955a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0l-4.24-4.52a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <div
              v-if="open"
              class="absolute right-0 mt-2 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
            >
              <div class="py-1">
                <button
                  v-for="lang in languages"
                  :key="lang.code"
                  @click="changeLanguage(lang.code)"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                >
                  {{ lang.label }}
                </button>
              </div>
            </div>
          </div>
            --> 
          <span class="text-gray-700 font-medium">{{ user.name }}</span>
          <form @submit.prevent="logout">
            <button
              type="submit"
              class="flex items-center space-x-2 text-red-600 hover:text-red-800 font-medium bg-transparent p-0 m-0 border-0 cursor-pointer"
            >
              <ArrowRightOnRectangleIcon class="w-5 h-5" />
              <span>{{ t('logout') }}</span>
            </button>
          </form>
        </div>
      </header>

      <!-- コンテンツ -->
      <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <slot />

        <Toast />
        <Toaster position="top-right" rich-colors  />

        <LoadingOverlay />
      </main>
    </div>
    </div>
    <!-- footer -->
    <ApplicationFooter />
  </div>    
</template>

<script setup>
import Navigation from '@/Layouts/Admin/Navigation.vue'
import Toast from '@/Components/Toast.vue'
import LoadingOverlay from '@/Components/LoadingOverlay.vue'
import ApplicationFooter from '@/Components/ApplicationFooter.vue'

import { router, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'
import { ArrowRightOnRectangleIcon, GlobeAltIcon, BellIcon } from '@heroicons/vue/24/outline'
import { useI18n } from 'vue-i18n'
import { Toaster } from '@/components/ui/sonner'

import axios from 'axios'

const { props } = usePage()
const user = props.auth.user

//const { t } = useI18n()
const { t, messages, locale } = useI18n();

const logout = () => {
  router.post(route('admin.logout'))
}
const toggleMenu = () => {
  // Navigation.vueのisOpenをグローバル管理にする場合はPinia等で対応可
  // 今回は簡易対応のためにイベントを投げる
  window.dispatchEvent(new Event('toggle-navigation'))
}
// ドロップダウン開閉フラグ
const open = ref(false)

// 切り替え可能な言語
const languages = [
  { code: 'ja', label: '日本語' },
  { code: 'en', label: 'English' },
]

// 言語変更
const changeLanguage = (lang) => {
  locale.value = lang
  open.value = false
}

// ──────────────────────────────────────────
// Webhook通知
// ──────────────────────────────────────────

const unreadCount    = ref(0)
const webhookLogs     = ref([])
const webhookLogsOpen = ref(false)
let pollTimer = null

const fetchUnreadCount = async () => {
  try {
    const { data } = await axios.get(route('admin.webhook_logs.unread_count'))
    unreadCount.value = data.count
  } catch (e) {
    console.error('Webhook通知件数の取得に失敗しました', e)
  }
}

const fetchWebhookLogs = async () => {
  try {
    const { data } = await axios.get(route('admin.webhook_logs.index'))
    webhookLogs.value = data.logs
  } catch (e) {
    console.error('Webhook通知一覧の取得に失敗しました', e)
  }
}

const toggleWebhookLogs = async () => {
  webhookLogsOpen.value = !webhookLogsOpen.value
  if (webhookLogsOpen.value) {
    await fetchWebhookLogs()
  }
}

const markAllRead = async () => {
  try {
    await axios.post(route('admin.webhook_logs.mark_all_read'))
    unreadCount.value = 0
    webhookLogs.value = webhookLogs.value.map(l => ({ ...l, is_read: true }))
  } catch (e) {
    console.error('既読化に失敗しました', e)
  }
}

const formatDate = (datetime) => {
  if (!datetime) return ''
  const d = new Date(datetime)
  return d.toLocaleString('ja-JP', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchUnreadCount()
  // 60秒ごとに未読件数をポーリング
  pollTimer = setInterval(fetchUnreadCount, 60000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>