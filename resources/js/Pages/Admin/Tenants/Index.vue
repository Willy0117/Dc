<template>
  <AppLayout>
    <template #header>{{ t('tenants.tenant_list') }}</template>

    <!-- 検索トリガーボタン -->
    <div dir="rtl">
      <div class="relative size-4">
        <div class="absolute start-0 top-0 size-14">
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
      <!-- 右側 Drawer -->
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
            <!-- 検索フォーム -->
            <input v-model="form.name" type="text" :placeholder="t('tenants.name')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.contact_email" type="text" :placeholder="t('email')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.contact_phone" type="text" :placeholder="t('tenants.tel')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.address" type="text" :placeholder="t('tenants.address')" class="border rounded px-3 py-2 w-full" />

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

      <!-- per_page + Add Tenant -->
      <div class="flex flex-wrap md:flex-nowrap md:justify-between mb-4 items-center gap-2 text-sm">
        <div class="flex items-center gap-2">
          <select
            v-model.number="form.per_page"
            @change="submitSearch"
            class="border rounded px-3 py-2 w-16 h-10"
          >
            <option v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</option>
          </select>

          <Link
            :href="route('admin.tenants.create', persistQuery())"
            class="px-4 h-10 bg-green-500 text-white rounded hover:bg-green-600 flex items-center space-x-1"
          >
            <PlusIcon class="w-4 h-4"/>
            <span>{{ t('tenants.add_tenant') }}</span>
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

      <!-- Tenant 一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('name')">{{ t('tenants.name') }}
              <span v-if="form.sort==='name'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('contact_email')">{{ t('email') }}
              <span v-if="form.sort==='contact_email'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('contact_phone')">{{ t('tenants.tel') }}
              <span v-if="form.sort==='contact_phone'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('address')">{{ t('tenants.address') }}
              <span v-if="form.sort==='address'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2">{{ t('updated_at') }}</th>
            <th class="px-3 py-2 text-center">{{ t('actions.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tenant in tenants.data" :key="tenant.id" class="odd:bg-white even:bg-gray-100">
            <td class="px-3 py-2">
              <input type="checkbox" :value="tenant.id" v-model="selectedIds" />
            </td>
            <td class="px-3 py-2">{{ tenant.name }}</td>
            <td class="px-3 py-2">{{ tenant.contact_email }}</td>
            <td class="px-3 py-2">{{ tenant.contact_phone }}</td>
            <td class="px-3 py-2">{{ tenant.address }}</td>
            <td class="px-3 py-2">{{ tenant.updated_at ? dayjs(tenant.updated_at).format('YYYY/MM/DD HH:mm:ss') : '' }}</td>
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <button @click="copyTenant(tenant.id)" class="text-green-500 hover:text-green-700">
                <DocumentDuplicateIcon class="w-4 h-4" />
              </button>
              <Link :href="route('admin.tenants.edit', { tenant: tenant.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link>
              <button @click="deleteTenant(tenant.id)" class="text-red-500 hover:text-red-700">
                <TrashIcon class="w-4 h-4"/>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :paginator="tenants" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
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
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentDuplicateIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  tenants: Object,
  filters: {
    type: Object,
    default: () => ({
      name: '', contact_email: '', contact_phone: '', address: '',
      per_page: 20, sort: 'id', direction: 'asc', page: 1
    })
  }
})

const { t } = useI18n()
const openDrawer = ref(false)
const form = reactive({
  name: props.filters.name,
  contact_email: props.filters.contact_email,
  contact_phone: props.filters.contact_phone,
  address: props.filters.address,
  per_page: props.filters.per_page??20,
  sort: props.filters.sort,
  direction: props.filters.direction
})
const selectedIds = ref([])

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.tenants.data.map(t => t.id) : []
}
const resetSelectedIds = () => { selectedIds.value = [] }
const selectAll = computed(() => selectedIds.value.length === props.tenants.data.length)
watch(() => props.tenants.current_page, () => { selectedIds.value = [] })

const persistQuery = () => ({
  name: form.name,
  contact_email: form.contact_email,
  contact_phone: form.contact_phone,
  address: form.address,
  per_page: form.per_page,
  sort_by: form.sort,
  sort_dir: form.direction,
  page: props.tenants.current_page
})

const submitSearch = () => {
  router.get(route('admin.tenants.index'), { ...persistQuery(), page: 1 }, { preserveState: true, replace: true, onSuccess: resetSelectedIds })
}

const goPage = (page) => {
  router.get(route('admin.tenants.index'), { ...persistQuery(), page }, { preserveState: true, replace: true, onSuccess: resetSelectedIds })
}

const sortBy = (field) => {
  if (form.sort === field) form.direction = form.direction === 'asc' ? 'desc' : 'asc'
  else { form.sort = field; form.direction = 'asc' }
  submitSearch()
}

const deleteTenant = (tenant_id) => {
  if (!confirm(t('confirm_delete'))) return
  router.delete(route('admin.tenants.destroy', tenant_id), { preserveState: true, onSuccess: () => submitSearch() })
}

const bulkDelete = () => {
  if (!confirm(t('confirm_delete_selected'))) return
  router.post(route('admin.tenants.bulkDelete'), { ids: selectedIds.value }, { preserveState: true, onSuccess: () => submitSearch() })
}

const copyTenant = (tenant_id) => {
  router.get(route('admin.tenants.create', { ...persistQuery(), mode: 'copy', tenant_id }))
}

const startItem = computed(() => props.tenants.per_page * (props.tenants.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.tenants.per_page * props.tenants.current_page, props.tenants.total))
</script>
