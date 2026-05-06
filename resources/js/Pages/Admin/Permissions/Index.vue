<template>
  <AppLayout>
    <template #header>{{ t('permissions.permission_list') }}</template>

    <div class="p-6">
      <!-- per_page + Add Permission -->
      <div class="flex flex-wrap md:flex-nowrap md:justify-between mb-4 items-center gap-2">
        <div class="flex items-center gap-2">
          <select
            v-model.number="form.per_page"
            @change="submitSearch"
            class="border rounded px-3 py-2 w-16 h-10"
          >
            <option v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</option>
          </select>

          <Link
            :href="route('admin.permissions.create', persistQuery())"
            class="px-4 h-10 bg-green-500 text-white rounded hover:bg-green-600 flex items-center space-x-1"
          >
            <PlusIcon class="w-4 h-4"/>
            <span>{{ t('permissions.add_permission') }}</span>
          </Link>
        </div>

        <button
          @click="bulkDelete"
          :disabled="selectedIds.length === 0"
          class="px-4 h-10 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50 flex items-center space-x-1"
        >
          <TrashIcon class="w-4 h-4"/>
          <span>{{ t('delete_selected') }}</span>
        </button>
      </div>

      <!-- Permission 一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th v-if="isSuperAdmin">{{ t('tenant') }}</th>            
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('name')">{{ t('name') }}
              <span v-if="form.sort==='name'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 text-center">{{ t('updated_at') }}</th>
            <th class="px-3 py-2 text-center">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="permission in permissions.data" :key="permission.id" class="odd:bg-white even:bg-gray-100">
            <td class="px-3 py-2">
              <input type="checkbox" :value="permission.id" v-model="selectedIds" />
            </td>
            <td v-if="isSuperAdmin">
              {{ tenants.find(t => t.id === permission.tenant_id)?.name || '-' }}
            </td>            
            <td class="px-3 py-2">{{ permission.name }}</td>
            <td class="px-3 py-2 text-center">{{ permission.updated_at ? dayjs(permission.updated_at).format('YYYY/MM/DD HH:mm:ss') : '' }}</td>
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <button @click="copyPermission(permission.id)" class="text-green-500 hover:text-green-700">
                <DocumentDuplicateIcon class="w-4 h-4" />
              </button>
              <Link :href="route('admin.permissions.edit', { permission: permission.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link>
             <!-- 新規: 割り当てボタン -->
              <Link :href="route('admin.permissions.assign', permission.id)" class="text-green-500 hover:text-green-700">
                <UserIcon class="w-4 h-4" />
              </Link>
              <button @click="deletePermission(permission.id)" class="text-red-500 hover:text-red-700">
                <TrashIcon class="w-4 h-4"/>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :paginator="permissions" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, DocumentDuplicateIcon, UserIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  permissions: Object,
  tenants: Array, // Super Admin 用
  user: Object,   // ← これが必要  
  filters: {
    type: Object,
    default: () => ({
      name: '',
      per_page: 20,
      sort: 'id',
      direction: 'asc',
      page: 1
    })
  }
})

const { t } = useI18n()

const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super admin')
)

const form = reactive({
  name: props.filters.name,
  per_page: props.filters.per_page??20,
  sort: props.filters.sort,
  direction: props.filters.direction
})
const selectedIds = ref([])

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.permissions.data.map(p => p.id) : []
}
const resetSelectedIds = () => { selectedIds.value = [] }
const selectAll = computed(() => selectedIds.value.length === props.permissions.data.length)
watch(() => props.permissions.current_page, () => { selectedIds.value = [] })

const persistQuery = () => ({
  name: form.name,
  per_page: form.per_page,
  sort: form.sort,
  direction: form.direction,
  page: props.permissions.current_page
})

const submitSearch = () => {
  router.get(route('admin.permissions.index'), { ...persistQuery(), page: 1 }, { preserveState: true, replace: true, onSuccess: resetSelectedIds })
}

const goPage = (page) => {
  router.get(route('admin.permissions.index'), { ...persistQuery(), page }, { preserveState: true, replace: true, onSuccess: resetSelectedIds })
}

const sortBy = (field) => {
  if (form.sort === field) form.direction = form.direction === 'asc' ? 'desc' : 'asc'
  else { form.sort = field; form.direction = 'asc' }
  submitSearch()
}

const deletePermission = (permission_id) => {
  if (!confirm(t('confirm_delete'))) return
  router.delete(route('admin.permissions.destroy', permission_id), { preserveState: true, onSuccess: () => submitSearch() })
}

const bulkDelete = () => {
  if (!confirm(t('confirm_delete_selected'))) return
  router.post(route('admin.permissions.bulkDelete'), { ids: selectedIds.value }, { preserveState: true, onSuccess: () => submitSearch() })
}

const copyPermission = (permission_id) => {
  router.get(route('admin.permissions.create', { ...persistQuery(), mode: 'copy', permission_id }))
}

const startItem = computed(() => props.permissions.per_page * (props.permissions.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.permissions.per_page * props.permissions.current_page, props.permissions.total))
</script>
