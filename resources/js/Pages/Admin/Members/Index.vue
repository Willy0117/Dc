<template>
  <AppLayout title="会員一覧">
    <template #header>{{ t('members_list') }}</template>

    <!-- 検索・追加 -->
    <div class="p-4 flex justify-between items-center">
      <div class="flex items-center gap-2">
        <input v-model="form.login_id" type="text" :placeholder="t('login_id')" class="border rounded px-3 py-2"/>
        <input v-model="form.name" type="text" :placeholder="t('name')" class="border rounded px-3 py-2"/>
        <button @click="submitSearch" class="bg-blue-500 text-white px-4 py-2 rounded">{{ t('search') }}</button>
      </div>

      <Link :href="route('admin.members.create', persistQuery())"
            class="bg-green-500 text-white px-4 py-2 rounded">{{ t('add_member') }}</Link>
    </div>

    <!-- テーブル -->
    <table class="min-w-full table-auto border border-gray-300 mt-4">
      <thead>
        <tr class="bg-gray-200">
          <th class="px-3 py-2">ID</th>
          <th class="px-3 py-2">{{ t('login_id') }}</th>
          <th class="px-3 py-2">{{ t('name') }}</th>
          <th class="px-3 py-2">{{ t('phone') }}</th>
          <th class="px-3 py-2">{{ t('address') }}</th>
          <th class="px-3 py-2">{{ t('status') }}</th>
          <th class="px-3 py-2">{{ t('actions') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="member in members.data" :key="member.id" class="odd:bg-white even:bg-gray-100">
          <td class="px-3 py-2">{{ member.id }}</td>
          <td class="px-3 py-2">{{ member.login_id }}</td>
          <td class="px-3 py-2">{{ member.name }}</td>
          <td class="px-3 py-2">{{ member.phone }}</td>
          <td class="px-3 py-2">{{ member.address }}</td>
          <td class="px-3 py-2">{{ member.status }}</td>
          <td class="px-3 py-2 space-x-1 flex">
            <Link :href="route('admin.members.edit', { member: member.id, ...persistQuery() })"
                  class="text-blue-500 hover:text-blue-700">{{ t('edit') }}</Link>
            <button @click="deleteMember(member.id)" class="text-red-500 hover:text-red-700">{{ t('delete') }}</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- ページネーション -->
    <Pagination :paginator="members" :onPageChange="goPage"/>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  members: Object,
  filters: Object
})

const { t } = useI18n()

const form = reactive({
  login_id: props.filters.login_id || '',
  name: props.filters.name || '',
  per_page: props.filters.per_page || 10,
  sort_by: props.filters.sort_by || 'id',
  sort_dir: props.filters.sort_dir || 'asc'
})

const persistQuery = () => ({ ...form, page: props.members.current_page })
const submitSearch = () => router.get(route('admin.members.index'), { ...persistQuery(), page: 1 }, { preserveState: true })
const goPage = (page) => router.get(route('admin.members.index'), { ...persistQuery(), page }, { preserveState: true })

const deleteMember = (id) => {
  if(confirm(t('confirm_delete'))) {
    router.delete(route('admin.members.destroy', id), { onSuccess: () => submitSearch() })
  }
}
</script>
