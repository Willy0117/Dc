<template>
  <AppLayout>
    <template #header>{{ t('menu_list') }}</template>

    <!-- 検索 Drawer トリガー -->
    <div class="p-4 flex justify-end">
      <button @click="openDrawer = true" class="p-2 rounded hover:bg-gray-200 flex items-center">
        <MagnifyingGlassIcon class="w-5 h-5 text-gray-600" />
      </button>
    </div>

    <!-- Drawer -->
    <div v-if="openDrawer" class="fixed inset-0 z-40">
      <div class="absolute inset-0 bg-black bg-opacity-30" @click="openDrawer = false"></div>
      <aside class="absolute top-0 right-0 h-full bg-white shadow-lg z-50 flex flex-col transition-all duration-300 overflow-hidden"
             :style="{ width: openDrawer ? '20rem' : '0rem' }">
        <div class="p-4 flex justify-between items-center border-b">
          <h2 class="text-lg font-bold">{{ t('search') }}</h2>
          <button @click="openDrawer = false" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>

        <div class="p-4 space-y-3">
          <div>
            <label class="block mb-1">{{ t('dish_name') }}</label>
            <input v-model="form.dish_name" type="text" :placeholder="t('dish_name')" class="border rounded px-3 py-2 w-full" />
          </div>
          <div>
            <label class="block mb-1">{{ t('serving_date') }}</label>
            <input v-model="form.serving_date_from" type="date" :placeholder="t('serving_date_from')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.serving_date_to" type="date" :placeholder="t('serving_date_to')" class="border rounded px-3 py-2 w-full" />
          </div>
          <input v-model="form.serving_time" type="time" :placeholder="t('serving_time')" class="border rounded px-3 py-2 w-full" />
          <div>
            <label class="block mb-1">{{ t('cooking_date') }}</label>
            <input v-model="form.cooking_date_from" type="date" :placeholder="t('cooking_date_from')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.cooking_date_to" type="date" :placeholder="t('cooking_date_to')" class="border rounded px-3 py-2 w-full" />
          </div>
          <textarea v-model="form.materials" :placeholder="t('materials')" class="border rounded px-3 py-2 w-full"></textarea>

          <div class="flex justify-end space-x-2 mt-4">
            <button @click="submitSearch(); openDrawer = false"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">{{ t('search') }}</button>
            <button @click="openDrawer = false"
                    class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">{{ t('close') }}</button>
          </div>
        </div>
      </aside>
    </div>

    <div class="p-6">
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

          <Link :href="route('menus.create', persistQuery())"
                class="px-4 h-10 bg-green-500 text-white rounded hover:bg-green-600 flex items-center space-x-1">
            <PlusIcon class="w-4 h-4"/>
            <span>{{ t('add_menu') }}</span>
          </Link>
        </div>

        <!-- 複数削除 -->
        <button @click="bulkDelete" :disabled="selectedIds.length===0"
                class="px-4 h-10 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50 flex items-center space-x-1">
          <TrashIcon class="w-4 h-4"/>
          <span>{{ t('delete_selected') }}</span>
        </button>
      </div>

      <!-- 献立テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th v-if="isSuperAdmin">{{ t('tenant') }}</th> 
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('dish_name')">
              {{ t('dish_name') }}
              <span v-if="form.sort==='dish_name'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('serving_date')">{{ t('serving_date') }}</th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('serving_time')">{{ t('serving_time') }}</th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('cooking_date')">{{ t('cooking_date') }}</th>
            <th class="px-3 py-2">{{ t('materials') }}</th>
            <th class="px-3 py-2 text-center">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="menu in menus.data" :key="menu.id" class="odd:bg-white even:bg-gray-100">
            <td class="px-3 py-2"><input type="checkbox" :value="menu.id" v-model="selectedIds" /></td>
            <td v-if="isSuperAdmin">
              {{ tenants.find(t => t.id === menu.tenant_id)?.name || '-' }}
            </td>            
            <td class="px-3 py-2">{{ menu.dish_name }}</td>
            <td class="px-3 py-2">{{ menu.serving_date ? dayjs(menu.serving_date).format('YYYY/MM/DD HH:mm:ss') : '' }}</td>
            <td class="px-3 py-2">{{ menu.serving_time }}</td>
            <td class="px-3 py-2">{{ menu.cooking_date ? dayjs(menu.cooking_date).format('YYYY/MM/DD HH:mm:ss') : '' }}</td>
            <td class="px-3 py-2">{{ menu.materials }}</td>
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <button @click="copyMenu(menu.id)" class="text-green-500 hover:text-green-700">
                <DocumentDuplicateIcon class="w-4 h-4" />
              </button>
              <Link :href="route('menus.edit', { menu: menu.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link>
              <button @click="deleteMenu(menu.id)" class="text-red-500 hover:text-red-700">
                <TrashIcon class="w-4 h-4"/>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="menus" :onPageChange="goPage"
                  :startItem="startItem" :endItem="endItem"/>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentDuplicateIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  menus: Object,
  tenants: Array,
  user: Object,
  filters: Object
})

const { t } = useI18n()

const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super admin')
)

// 検索フォーム
const openDrawer = ref(false)
const form = reactive({
  dish_name: props.filters.dish_name || '',
  serving_date_from: props.filters.serving_date_from || '',
  serving_date_to: props.filters.serving_date_to || '',
  serving_time: props.filters.serving_time || '',
  cooking_date_from: props.filters.cooking_date_from || '',
  cooking_date_to: props.filters.cooking_date_to || '',
  materials: props.filters.materials || '',
  per_page: props.filters.per_page || 10,
  sort: props.filters.sort || 'id',
  direction: props.filters.direction || 'asc'
})

// 選択削除
const selectedIds = ref([])
const toggleSelectAll = (checked) => { selectedIds.value = checked ? props.menus.data.map(s => s.id) : [] }
const selectAll = computed(() => selectedIds.value.length === props.menus.data.length)
watch(() => props.menus.current_page, () => selectedIds.value = [])

// persistQuery
const persistQuery = () => ({ ...form, page: props.menus.current_page })
const submitSearch = () => router.get(route('menus.index'), { ...persistQuery(), page: 1 }, { preserveState: true })
const goPage = (page) => router.get(route('menus.index'), { ...persistQuery(), page }, { preserveState: true })

// ソート
const sortBy = (field) => {
  if (form.sort === field) form.direction = form.direction==='asc'?'desc':'asc'
  else { form.sort = field; form.direction = 'asc' }
  submitSearch()
}

// 削除
const deleteMenu = (menu_id) => { if(confirm(t('confirm_delete'))) router.delete(route('menus.destroy', menu_id), { preserveState: true, onSuccess: () => submitSearch() }) }
const bulkDelete = () => { if(confirm(t('confirm_delete_selected'))) router.post(route('menus.bulkDelete'), { ids: selectedIds.value }, { preserveState: true, onSuccess: () => submitSearch() }) }

// コピー
const copyMenu = (menu_id) => router.get(route('menus.create', { ...persistQuery(), mode: 'copy', menu_id }))

// ページ番号計算
const startItem = computed(() => props.menus.per_page * (props.menus.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.menus.per_page * props.menus.current_page, props.menus.total))
</script>
