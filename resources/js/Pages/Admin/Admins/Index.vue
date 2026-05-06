<template>
  <AppLayout>
    <template #header>{{ t('admin_list') }}</template>
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
    <div class="p-6">
      <!-- 検索フォーム Drawer -->
      <div v-if="openDrawer" class="fixed inset-0 z-40">
        <div class="absolute inset-0 bg-black bg-opacity-30" @click="openDrawer = false"></div>
        <aside
          class="absolute top-0 right-0 h-full bg-white shadow-lg z-50 flex flex-col transition-all duration-300 overflow-hidden"
          :style="{ width: openDrawer ? '20rem' : '0rem' }"
        >      
          <div class="p-4 flex justify-between items-center border-b">
            <h2 class="text-lg font-bold">{{ t('search') }}</h2>
            <button @click="openDrawer = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <div class="p-4 space-y-3">
            <input v-model="form.code" type="text" placeholder="Code" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.name" type="text" placeholder="Name" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.email" type="text" placeholder="Email" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.tenant_id" type="number" placeholder="Tenant ID" class="border rounded px-3 py-2 w-full" />

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
            id="per_page"
            v-model.number="form.per_page"
            @change="submitSearch"
            class="border rounded px-3 py-2 h-10 w-24 pr-8 appearance-none bg-white text-gray-700"
          >
            <option v-for="n in [10,20,30,50]" :key="n" :value="n">
              {{ n }}
            </option>
          </select>

          <Link
            :href="route('admin.admins.create', persistQuery())"
            class="px-4 h-10 bg-green-500 text-white rounded hover:bg-green-600 flex items-center space-x-1"
          >
            <PlusIcon class="w-4 h-4"/>
            <span>{{ t('add_admin') }}</span>
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

      <!-- ユーザー一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-30 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('name')">
              {{ t('name') }}
              <span v-if="form.sort_by==='name'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('email')">
              {{ t('email') }}
              <span v-if="form.sort_by==='email'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2">{{ t('updated_at') }}</th>
            <th class="px-3 py-2 text-center">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="admin in admins.data" :key="admin.id" class="odd:bg-white even:bg-gray-100">
            <td class="px-3 py-2">
              <input type="checkbox" :value="admin.id" v-model="selectedIds" />
            </td>
            <td class="px-3 py-2">{{ admin.name }}</td>
            <td class="px-3 py-2">{{ admin.email }}</td>
            <td class="px-3 py-2">{{ admin.updated_at ? dayjs(admin.updated_at).format('YYYY/MM/DD HH:mm:ss') : '' }}</td>
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <button @click="copyUser(admin.id)" class="text-green-500 hover:text-green-700">
                <DocumentDuplicateIcon class="w-4 h-4" />
              </button>
              <Link :href="route('admin.admins.edit', { admin: admin.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link>
              <button @click="deleteUser(admin.id)" class="text-red-500 hover:text-red-700">
                <TrashIcon class="w-4 h-4"/>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="admins" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch, onMounted, watchEffect } from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, DocumentDuplicateIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  admins: Object,
  filters: Object
})

const { t } = useI18n()
const openDrawer = ref(false)
const form = reactive({
  code: props.filters.code || '',
  name: props.filters.name || '',
  email: props.filters.email || '',
  tenant_id: props.filters.tenant_id || '',
  per_page: props.filters.per_page || 20,
  sort_by: props.filters.sort_by || 'id',
  sort_dir: props.filters.sort_dir || 'desc'
})
const selectedIds = ref([])

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.admins.data.map(u => u.id) : []
}
const resetSelectedIds = () => { selectedIds.value = [] }
const selectAll = computed({
  get() { return selectedIds.value.length === props.admins.data.length }
})

watch(() => props.admins.current_page, () => resetSelectedIds())

const persistQuery = () => ({
  code: form.code,
  name: form.name,
  email: form.email,
  tenant_id: form.tenant_id,
  per_page: form.per_page,
  sort_by: form.sort,
  sort_dir: form.sort_dir,
  page: props.admins.current_page ?? 1,
})

watchEffect(() => {
  form.per_page = props.filters.per_page ?? 20;
});

const submitSearch = () => {
  router.get(route('admin.admins.index'), { ...persistQuery(), page: 1 }, { preserveState: true, replace: true, onSuccess: resetSelectedIds })
}

const goPage = (page) => {
  router.get(route('admin.admins.index'), { ...persistQuery(), page }, { preserveState: true, replace: true, onSuccess: resetSelectedIds })
}

const sortBy = (field) => {
  if (form.sort === field) form.sort_dir = form.sort_dir==='asc'?'desc':'asc'
  else { form.sort = field; form.sort_dir = 'asc' }
  submitSearch()
}

const deleteUser = (admin_id) => {
  if (!confirm(t('confirm_delete'))) return
  router.delete(route('admin.admins.destroy', admin_id), { preserveState: true, onSuccess: () => submitSearch() })
}

const bulkDelete = () => {
  if (!confirm(t('confirm_delete_selected'))) return
  router.post(route('admin.admins.bulkDelete'), { ids: selectedIds.value }, { preserveState: true, onSuccess: () => submitSearch() })
}

const copyUser = (admin_id) => {
  router.get(route('admin.admins.create', { ...persistQuery(), mode: 'copy', admin_id }))
}

const startItem = computed(() => props.admins.per_page * (props.admins.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.admins.per_page * props.admins.current_page, props.admins.total))
</script>

