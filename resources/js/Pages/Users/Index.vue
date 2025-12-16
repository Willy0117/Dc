<template>
  <AppLayout>
    <template #header>{{ t('user_list') }}</template>
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
    <div class="p-6 text-sm">
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
            <!--input v-model="form.code" type="text" :placeholder="t('code')" class="border rounded px-3 py-2 w-full" / -->
            <input v-model="form.name" type="text" :placeholder="t('name')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.email" type="text" :placeholder="t('email')" class="border rounded px-3 py-2 w-full" />
            <!--input v-model="form.tenant_id" type="number" :placeholder="t('tenant')" class="border rounded px-3 py-2 w-full" / -->
            <!-- Super Admin のときだけ表示 -->
            <div v-if="tenants && tenants.length">
              <select
                v-model.number="form.tenant_id"
                class="border rounded px-3 py-2 w-full"
              >
                <option :value="0"></option>
                <option
                  v-for="tenant in tenants"
                  :key="tenant.id"
                  :value="tenant.id"
                >
                  {{ tenant.name }}
                </option>
              </select>
            </div>
          
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

      <div class="flex flex-wrap md:flex-nowrap md:justify-between mb-4 items-center gap-2 text-sm">
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
            :href="route('users.create', persistQuery())"
            class="px-4 h-10 bg-green-500 text-white rounded hover:bg-green-600 flex items-center space-x-1"
          >
            <PlusIcon class="w-4 h-4"/>
            <span>{{ t('add_user') }}</span>
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
      <table class="min-w-full table-auto border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <!--th class="px-3 py-2 cursor-pointer" @click="sortBy('code')">
              {{ t('code') }}
              <span v-if="form.sort==='code'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th -->
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('name')">
              {{ t('name') }}
              <span v-if="form.sort==='name'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('email')">
              {{ t('email') }}
              <span v-if="form.sort==='email'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('tenant_id')">
              {{ t('tenant_id') }}
              <span v-if="form.sort==='tenant_id'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('role')">
              {{ t('role') }}
              <span v-if="form.sort==='role'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>

            <th class="px-3 py-2">{{ t('updated_at') }}</th>
            <th class="px-3 py-2 text-center">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users.data" :key="user.id" class="odd:bg-white even:bg-gray-100">
            <td class="px-3 py-2">
              <input type="checkbox" :value="user.id" v-model="selectedIds" />
            </td>
            <!-- td class="px-3 py-2">{{ user.code }}</td -->
            <td class="px-3 py-2">{{ user.name }}</td>
            <td class="px-3 py-2">{{ user.email }}</td>
            <td class="px-3 py-2">{{ user.tenant?.name || '-' }}</td>
            <td class="px-3 py-2">{{ user.roles?.[0]?.name ?? '-' }}</td>
            <td class="px-3 py-2">{{ user.updated_at ? dayjs(user.updated_at).format('YYYY/MM/DD HH:mm:ss') : '' }}</td>
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <button @click="copyUser(user.id)" class="text-green-500 hover:text-green-700">
                <DocumentDuplicateIcon class="w-4 h-4" />
              </button>
              <Link :href="route('users.edit', { user: user.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link>
              <button @click="deleteUser(user.id)" class="text-red-500 hover:text-red-700">
                <TrashIcon class="w-4 h-4"/>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="users" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, DocumentDuplicateIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  users: Object,
  filters: Object,
  tenants: Object
})

const { t } = useI18n()
const openDrawer = ref(false)

const form = reactive({
   code: props.filters.code || '',
   name: props.filters.name || '',
   email: props.filters.email || '',
   tenant_id: props.filters.tenant_id ? Number(props.filters.tenant_id) : 0,
   per_page: props.filters.per_page || 20,
   sort: props.filters.sort || 'id',
   direction: props.filters.direction || 'asc'
})

const selectedIds = ref([])

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.users.data.map(u => u.id) : []
}
const resetSelectedIds = () => { selectedIds.value = [] }

const selectAll = computed({
  get() { return selectedIds.value.length === props.users.data.length }
})

watch(() => props.users.current_page, () => resetSelectedIds())

const persistQuery = () => ({
  code: form.code,
  name: form.name,
  email: form.email,
  tenant_id: form.tenant_id,
  per_page: form.per_page,
  sort_by: form.sort,
  sort_dir: form.direction,
  page: props.users.current_page
})

const submitSearch = () => {
  router.get(route('users.index'), { ...persistQuery(), page: 1 }, { preserveState: true, replace: true, onSuccess: resetSelectedIds })
}

const goPage = (page) => {
  router.get(route('users.index'), { ...persistQuery(), page }, { preserveState: true, replace: true, onSuccess: resetSelectedIds })
}

const sortBy = (field) => {
  if (form.sort === field) form.direction = form.direction==='asc'?'desc':'asc'
  else { form.sort = field; form.direction = 'asc' }
  submitSearch()
}

const deleteUser = (user_id) => {
  if (!confirm(t('confirm_delete'))) return
  router.delete(route('users.destroy', user_id), { preserveState: true, onSuccess: () => submitSearch() })
}

const bulkDelete = () => {
  if (!confirm(t('confirm_delete_selected'))) return
  router.post(route('users.bulkDelete'), { ids: selectedIds.value }, { preserveState: true, onSuccess: () => submitSearch() })
}

const copyUser = (user_id) => {
  router.get(route('users.create', { ...persistQuery(), mode: 'copy', user_id }))
}

const startItem = computed(() => props.users.per_page * (props.users.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.users.per_page * props.users.current_page, props.users.total))
</script>

