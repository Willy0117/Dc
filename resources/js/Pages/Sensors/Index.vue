<template>
  <AppLayout>
    <template #header>{{ t('sensor_list') }}</template>
    <div dir="rtl">
      <!-- 検索 トリガーボタン -->
        <div class="relative size-4 ...">
          <div class="absolute start-0 top-0 size-14 ...">
              <button
              @click="openDrawer = true"
              class="p-2 rounded hover:bg-gray-200 flex items-center justify-center"
            >
              <MagnifyingGlassIcon class="w-5 h-5 text-gray-600" />
            </button>
          </div>
        </div>
    </div>

    <div class="p-6 text-sm">
      <!-- 右側 Drawer -->
      <div v-if="openDrawer" class="fixed inset-0 z-40">
        <!-- 背景オーバーレイ -->
        <div class="absolute inset-0 bg-black bg-opacity-30" @click="openDrawer = false"></div>

        <!-- 右側 Drawer -->
        <aside
          class="absolute top-0 right-0 h-full bg-white shadow-lg z-50 flex flex-col transition-all duration-300 overflow-hidden"
          :style="{ width: openDrawer ? '20rem' : '0rem' }"
        >      
          <div class="p-4 flex justify-between items-center border-b">
            <h2 class="text-lg font-bold">{{ t('search') }}</h2>
            <button @click="openDrawer = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <div class="p-4 space-y-3">
            <!-- 既存 form をそのまま利用 -->
            <!-- input v-model="form.code" type="text" placeholder="Code" class="border rounded px-3 py-2 w-full" / -->
            <input v-model="form.name" type="text" :placeholder="t('name')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.model" type="text" :placeholder="t('model')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.serial_number" type="text" :placeholder="t('serial number')" class="border rounded px-3 py-2 w-full" />

            <div class="flex justify-end space-x-2 mt-4">
              <button @click="submitSearch(); openDrawer = false"
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                {{ t('search') }}
              </button>
              <button @click="openDrawer = false"
                      class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                {{ t('close') }}
              </button>
            </div>
          </div>
        </aside>
      </div>       

      <div class="flex flex-wrap md:flex-nowrap md:justify-between mb-4 items-center gap-2">

        <!-- per_page + add -->
        <div class="flex items-center gap-2">
          <select
            v-model.number="form.per_page"
            @change="submitSearch"
            class="border rounded px-3 py-2 w-16 h-10"
          >
            <option v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</option>
          </select>

          <Link
            :href="route('sensors.create', persistQuery())"
            class="px-4 h-10 bg-green-500 text-white rounded hover:bg-green-600 flex items-center space-x-1"
          >
            <PlusIcon class="w-4 h-4"/>
            <span>{{ t('add_sensor') }}</span>
          </Link>
        </div>

        <!-- 複数削除ボタン -->
        <button
          @click="bulkDelete"
          :disabled="selectedIds.length === 0"
          class="px-4 h-10 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50 flex items-center space-x-1"
        >
          <TrashIcon class="w-4 h-4"/>
          <span>{{ t('delete_selected') }}</span>
        </button>
      </div>

      <!-- センサー一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th v-if="isSuperAdmin">{{ t('tenant') }}</th>            
            <!-- th class="px-3 py-2 cursor-pointer" @click="sortBy('code')">
              {{ t('code') }}
              <span v-if="form.sort==='code'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th -->
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('name')">
              {{ t('name') }}
              <span v-if="form.sort==='name'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('model')">
              {{ t('model') }}
              <span v-if="form.sort==='model'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('serial_number')">
              {{ t('serial_number') }}
              <span v-if="form.sort==='serial_number'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2">{{ t('updated_at') }}</th>
            <th class="px-3 py-2 text-center">{{ t('disabled') }}</th>
            <th class="px-3 py-2 text-center">{{ t('display_order') }}</th>
            <th class="px-3 py-2 text-center">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sensor in sensors.data" :key="sensor.id" class="odd:bg-white even:bg-gray-100">

            <td class="px-3 py-2">
              <input type="checkbox" :value="sensor.id" v-model="selectedIds" />
            </td>
            <td v-if="isSuperAdmin">
              {{ tenants.find(t => t.id === sensor.tenant_id)?.name || '-' }}
            </td>            
            <!-- td class="px-3 py-2">{{ sensor.code }}</td -->
            <td class="px-3 py-2">{{ sensor.name }}</td>
            <td class="px-3 py-2">{{ sensor.model }}</td>
            <td class="px-3 py-2">{{ sensor.serial_number }}</td>
            <td class="px-3 py-2">{{ sensor.updated_at ? dayjs(sensor.updated_at).format('YYYY/MM/DD HH:mm:ss') : '' }}</td>
            <td class="px-3 py-2 text-center">{{ sensor.disabled ? t('enable') : t('disable') }}</td>
            <td class="px-3 py-2 text-center">{{ sensor.display_order }}</td>
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <button @click="copySensor(sensor.id)" class="text-green-500 hover:text-green-700">
                <DocumentDuplicateIcon class="w-4 h-4" />
              </button>
              <Link :href="route('sensors.edit', { sensor: sensor.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link>
              <button @click="deleteSensor(sensor.id)" class="text-red-500 hover:text-red-700">
                <TrashIcon class="w-4 h-4"/>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="sensors" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentDuplicateIcon} from '@heroicons/vue/24/outline'

const props = defineProps({
  tenants: Array, // Super Admin 用
  user: Object,   // ← これが必要  
  sensors: Object,
  filters: {
    type: Object,
    default: () => ({
      code: '', name: '', model: '', serial_number: '',
      per_page: 20, sort: 'id', direction: 'asc', page: 1
    })
  }
})

const { t } = useI18n()
const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super admin')
)

// 検索フォーム・per_page・sort・directionを reactive で管理
const openDrawer = ref(false)

// 複数検索用に reactive 拡張
const form = reactive({
  code: props.filters.code,
  name: props.filters.name,
  model: props.filters.model,
  serial_number: props.filters.serial_number,
  per_page: props.filters.per_page,
  sort: props.filters.sort,
  direction: props.filters.direction
})
// 選択削除
const selectedIds = ref([])

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.sensors.data.map(s => s.id) : []
}

const resetSelectedIds = () => {
  selectedIds.value = []
}

const selectAll = computed({
  get() {
    return selectedIds.value.length === props.sensors.data.length
  }
})

watch(() => props.sensors.current_page, () => {
  selectedIds.value = []
})


// persistQueryに各検索項目を追加
const persistQuery = () => ({
  code: form.code,
  name: form.name,
  model: form.model,
  serial_number: form.serial_number,
  per_page: form.per_page,
  sort_by: form.sort,
  sort_dir: form.direction,
  page: props.sensors.current_page
})

const submitSearch = () => {
  router.get(route('sensors.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// ページ番号クリック
const goPage = (page) => {
  router.get(route('sensors.index'), { ...persistQuery(), page }, {
    preserveState: true,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// 列ヘッダクリックでソート
const sortBy = (field) => {
  if (form.sort === field) form.direction = form.direction==='asc'?'desc':'asc'
  else { form.sort = field; form.direction = 'asc' }
  submitSearch()
}

// 行単位削除
const deleteSensor = (sensor_id) => {
  if (!confirm(t('confirm_delete'))) return
  router.delete(route('sensors.destroy', sensor_id), {
    preserveState: true,
    onSuccess: () => {
      router.get(route('sensors.index'), { ...persistQuery(), page: props.sensors.current_page }, { preserveState: true })
    }
  })
}
// 複数削除
const bulkDelete = () => {
  if (!confirm(t('confirm_delete_selected'))) return
  router.post(
    route('sensors.bulkDelete'),
    { ids: selectedIds.value },
    {
      preserveState: true,
      onSuccess: () => {
        // 削除後に検索条件・ページを保持して再取得
        router.get(route('sensors.index'), { ...persistQuery(), page: props.sensors.current_page }, { preserveState: true })
      }
    }
  )
}
// コピー機能追加
const copySensor = (sensor_id) => {
  router.get(
    route('sensors.create', { ...persistQuery(), mode: 'copy', sensor_id })
  )
}

// 表示件数計算
const startItem = computed(() => props.sensors.per_page * (props.sensors.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.sensors.per_page * props.sensors.current_page, props.sensors.total))
</script>




