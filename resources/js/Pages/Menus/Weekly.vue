<template>
  <AppLayout>
    <template #header>{{ t('weekly_menu') }}</template>

      <h2 class="text-lg font-semibold mb-4">{{ t('weekly_menu') }}</h2>
      <div class="p-6 space-y-4">
        <div class="flex justify-between items-center">
          <Link
            :href="route('menus.create', { redirect_to })"
            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
          >
            ＋ {{ t('add_menu') }}
          </Link>
          <div class="inline-flex border rounded overflow-hidden">
            <!-- 前の週ボタン -->
            <button
              type="button"
              @click="changeWeek(-1)"
              class="px-3 py-1 bg-white hover:bg-gray-100 border-r"
            >
              ←
            </button>

            <!-- カレンダーアイコン + 文字 -->
            <div class="px-4 py-1 flex items-center bg-white border-r">
              <CalendarIcon class="w-5 h-5 mr-1"/>
              <span>{{ t('week') }}</span>
            </div>

            <!-- 次の週ボタン -->
            <button
              type="button"
              @click="changeWeek(1)"
              class="px-3 py-1 bg-white hover:bg-gray-100"
            >
              →
            </button>
          </div>


      </div>
      <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full border border-gray-300 table-auto">
          <thead class="bg-gray-100 text-sm font-semibold">
            <tr>
              <th class="border px-2 py-2 w-36 text-center">{{ t('serving_time') }}</th>
              <th v-for="date in weekDays" :key="date" class="border px-2 py-2 text-center"
                :class="{
                  'text-red-500': dayjs(date).day() === 0,
                  'text-blue-500': dayjs(date).day() === 6
                }"
              >
                {{ formatDateShort(date) }} <span class="text-xs">({{ weekdayJP(date) }})</span>
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="time in state.servingTimes" :key="time" class="odd:bg-white even:bg-gray-50">
              <td class="border px-2 py-2 text-center font-medium bg-gray-50">{{ formatTime(time) }}</td>

              <td v-for="date in weekDays" :key="date + '-' + time" class="border px-2 py-2 align-top">
                <div v-if="menuData[date] && menuData[date][time]">
                    <div v-for="menu in menuData[date][time]" :key="menu.id" class="mb-1">
                      <Link
                        :href="route('menus.edit', { menu: menu.id, redirect_to: route('menus.weekly', { weekStart }) })"
                        class="text-blue-600 hover:underline block"
                        :title="menu.dish_name"
                      >
                        {{ truncate(menu.dish_name, 20) }}
                      </Link>
                    </div>
                </div>
                <div v-else class="text-gray-400 text-center">-</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive,computed } from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import 'dayjs/locale/ja'
// Heroicons
import {
  CalendarIcon,
} from '@heroicons/vue/24/outline'

dayjs.locale('ja')

const { t } = useI18n()

const props = defineProps({
  menuData: Object,        // { '2025-11-03': { '08:00': [ {id:1,name:'...'} ] } }
  servingTimes: Array,     // ['08:00', '12:00', '18:00']
  weekStart: String,        // '2025-11-03'
  redirect_to: String,
})

const form = reactive({
  redirect_to: props.redirect_to, // ← ここ重要
})

const getMonday = (dateStr) => {
  const date = dayjs(dateStr)
  const day = date.day() // 0=日, 1=月, ... 6=土
  return date.add(day === 0 ? -6 : 1 - day, 'day')
}

const state = reactive({
  currentWeekStart: getMonday(props.weekStart).format('YYYY-MM-DD'),
  servingTimes: props.servingTimes
})

// 週の日付リスト（currentWeekStart から7日分）
const weekDays = computed(() => {
  const start = dayjs(state.currentWeekStart)
  const arr = []
  for (let i = 0; i < 7; i++) {
    arr.push(start.add(i, 'day').format('YYYY-MM-DD'))
  }
  return arr
})

// 前後の週に切り替え
// 前後の週に切り替え
const changeWeek = (diff) => {
  const newWeekStart = getMonday(dayjs(state.currentWeekStart).add(diff * 7, 'day')).format('YYYY-MM-DD')
  state.currentWeekStart = newWeekStart
  fetchWeekData(newWeekStart)
}

// サーバーから指定週の献立データを取得
const fetchWeekData = (weekStart) => {
  router.get(
    route('menus.weekly'),
    { weekStart },
    {
      preserveState: true, // 他のページ状態は保持
      only: ['menuData','servingTimes'],  // 必要なpropsだけ更新
      onSuccess: (page) => {
        state.menuData = page.props.menuData,
        state.servingTimes = page.props.servingTimes
      }
    }
  )
}
// 表示ヘルパー
const formatDateShort = (dateStr) => dayjs(dateStr).format('MM/DD')
const weekdayJP = (dateStr) => ['日','月','火','水','木','金','土'][dayjs(dateStr).day()]
const formatTime = (timeStr) => timeStr ? timeStr.slice(0,5) : ''
const truncate = (text = '', max = 20) => text.length > max ? text.slice(0,max) + '…' : text
</script>

<style scoped>
/* 必要なら微調整 */
</style>




