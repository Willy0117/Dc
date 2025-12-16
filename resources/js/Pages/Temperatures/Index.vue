<template>
  <AppLayout>
    <template #header>{{ t('temperature') }}</template>

    <div class="p-6 space-y-4">

     <!-- 検索フォーム -->
      <div class="grid grid-cols-5 gap-2 items-end">
          
          <!-- Menu Autocomplete -->
          <Autocomplete
            v-model="form.menu_id"
            :label="t('dish_name')"
            placeholder="Type dish name / serving date"
            fetch-url="/menus/autocomplete"
          />

          <Autocomplete
            v-model="form.sensor_id"
            :label="t('sensor')"
            placeholder="Select sensor"
            fetch-url="/sensors/autocomplete"
          />

          <Autocomplete
            v-model="form.device_id"
            :label="t('device')"
            placeholder="Select device"
            fetch-url="/devices/autocomplete"
          />

          <Autocomplete
            v-model="form.operator_id"
            :label="t('operator')"
            placeholder="Select operator / type for search"
            fetch-url="/operators/autocomplete"
          />

          <!-- Handy No -->
          <div>
            <label class="block text-sm font-medium mb-1">{{ t('handy_no') }}</label>
            <input type="text" v-model="form.handy_no" placeholder="Handy No" class="border rounded px-3 py-2 w-full"/>
          </div>
      </div>
      <div class="grid grid-cols-5 gap-2 items-end">

          <!-- 2行目：日付 + 献立日/調理日 + 検索ボタン -->
          <!-- From日 -->
          <div class="relative">
            <label class="block text-sm font-medium mb-1">{{ t('from') }}</label>
            <div class="relative flex items-center">
              <input 
                type="date" 
                v-model="form.date_from" 
                class="w-full border rounded px-3 py-2 pr-8" 
              />
              <button 
                v-if="form.date_from"
                @click="form.date_from = ''"
                type="button"
                class="absolute right-2 text-gray-400 hover:text-gray-600 flex items-center justify-center h-full"
              >
                X
              </button>
            </div>
          </div>

          <!-- To日 -->
          <div class="relative">
            <label class="block text-sm font-medium mb-1">{{ t('to') }}</label>
            <div class="relative flex items-center">
              <input 
                type="date" 
                v-model="form.date_to" 
                class="w-full border rounded px-3 py-2 pr-8" 
              />
              <button 
                v-if="form.date_to"
                @click="form.date_to = ''"
                type="button"
                class="absolute right-2 text-gray-400 hover:text-gray-600 flex items-center justify-center h-full"
              >
                X
              </button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">{{ t('date_type') }}</label>
            <select v-model="form.date_type" class="border rounded px-3 py-2 pr-8 appearance-none">
              <option value="serving">{{ t('serving_date') }}</option>
              <option value="cooking">{{ t('cooking_date') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">{{ t('page') }}</label>
            <select
              v-model.number="form.per_page"
              @change="submitSearch"
              class="border rounded px-3 py-2 w-16 h-10"
            >
              <option v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
        <!-- 検索ボタンを右端 -->
          <button @click="submitSearch" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            {{ t('search') }}
          </button>
      </div>

      <!-- ログ一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('menu_id')">
              {{ t('dish_name') }}
              <span v-if="form.sort==='menu_id'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th
              class="px-3 py-2 cursor-pointer"
              @click="toggleDateSort"
            >
              {{ form.date_type === 'serving' ? t('serving_date') : t('cooking_date') }}
              <span v-if="form.sort === 'menu_date'">
                {{ form.direction === 'asc' ? '▲' : '▼' }}
              </span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('sensor_id')">
              {{ t('sensor') }}
              <span v-if="form.sort==='sensor_id'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('device_id')">
              {{ t('device') }}
              <span v-if="form.sort==='device_id'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('operator_id')">
              {{ t('operator') }}
              <span v-if="form.sort==='operator_id'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('handy_no')">
              {{ t('handy_no') }}
              <span v-if="form.sort==='handy_no'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th>
              {{ t('temperatures') }}
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('updated_at')">
              {{ t('updated_at') }}
              <span v-if="form.sort==='updated_at'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs.data" :key="log.id" class="odd:bg-white even:bg-gray-100">
            <td>{{ log.menu ? log.menu.dish_name : '-' }}</td>
            <td>{{ log.menu 
                ? (form.date_type === 'serving' 
                    ? dayjs(log.menu.serving_date).format('YYYY-MM-DD') 
                    : dayjs(log.menu.cooking_date).format('YYYY-MM-DD')) 
                : '-' 
                }}        
            </td>
            <td>{{ log.sensor ? log.sensor.name : '-' }}</td>
            <td>{{ log.device ? log.device.name : '-' }}</td>
            <td>{{ log.operator ? log.operator.name : '-' }}</td>
            <td class="mr px-3 py-2">{{ log.handy_no }}</td>
            <td>
                <ul>
                    <li v-for="temp in log.temperatures" :key="temp.recorded_at">
                        {{ temp.value }} ℃ ({{ dayjs(temp.datetime).format('YYYY/MM/DD HH:mm') }})
                    </li>
                </ul>
            </td>
            <td class="px-3 py-2">{{ log.updated_at ? dayjs(log.updated_at).format('YYYY/MM/DD HH:mm') : '' }}</td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="logs" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Autocomplete from '@/Components/Autocomplete.vue'

import { ref, reactive, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  logs: Object,
  tenants: Array,
  user: Object,
  filters: Object
})

const { t } = useI18n()

// Form
const form = reactive({
  menu_id: props.filters.menu_id || '',
  sensor_id: props.filters.sensor_id || '',
  device_id: props.filters.device_id || '',
  operator_id: props.filters.operator_id || '',
  handy_no: props.filters.handy_no || '',
  per_page: props.filters.per_page || 20,
  sort: props.filters.sort_by || 'id',
  direction: props.filters.sort_dir || 'asc',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  date_type: props.filters.date_type || 'serving'
})
// persistQueryに各検索項目を追加
const persistQuery = () => ({
  menu_id: form.menu_id || '',
  sensor_id: form.sensor_id || '',
  device_id: form.device_id || '',
  operator_id: form.operator_id || '',
  handy_no: form.filters.handy_no || '',
  date_from: form.filters.date_from || '',
  date_to: form.filters.date_to || '',
  date_type: form.date_type || 'serving',
  per_page: form.per_page,
  sort_by: form.sort,
  sort_dir: form.direction,
  page: props.sensors.current_page
})

const startItem = computed(() => props.logs.per_page * (props.logs.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.logs.per_page * props.logs.current_page, props.logs.total))

// Search
const submitSearch = () => { router.get(route('temperatures.index'), {...form,page:1}, {preserveState:true}) }
const goPage = (page) => { router.get(route('temperatures.index'), {...form,page}, {preserveState:true}) }

const toggleDateSort = () => {
  form.sort = 'menu_date'
  form.direction = form.direction === 'asc' ? 'desc' : 'asc'
  submitSearch()
}
// Sort
const sortBy = (field) => {
  if (form.sort===field) form.direction=form.direction==='asc'?'desc':'asc'
  else { form.sort=field; form.direction='asc' }
  submitSearch()
}

</script>



