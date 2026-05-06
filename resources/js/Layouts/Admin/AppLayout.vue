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
          <div class="relative inline-block text-left">
            <!-- トリガーボタン -->
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

            <!-- ドロップダウンメニュー -->
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

        <LoadingOverlay />
      </main>
    </div>
    </div>
    <!-- footer -->
    <footer class="border-t text-center text-xs text-gray-400 py-4">
      &copy {{ t('copyright') }}
    </footer>
  </div>    
</template>

<script setup>
import Navigation from '@/Layouts/Admin/Navigation.vue'
import Toast from '@/Components/Toast.vue'
import LoadingOverlay from '@/Components/LoadingOverlay.vue'

import { router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { ArrowRightOnRectangleIcon, GlobeAltIcon } from '@heroicons/vue/24/outline'
import { useI18n } from 'vue-i18n'

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
</script>