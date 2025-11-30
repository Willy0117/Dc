<template>
  <AppLayout :title="t('credit_conferences')">
    <template #header>{{ t('credit_conferences') }}</template>

    <div class="p-6 space-y-4">
      <div class="flex space-x-2 mb-4">
        <select v-model="selectedCategory" class="input" @change="filterConferences">
          <option :value="0">{{ t('all') }}</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
        </select>

        <inertia-link :href="route('admin.credit-conferences.create')" class="btn btn-primary">
          {{ t('create') }}
        </inertia-link>
      </div>

      <table class="table-auto w-full border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="border px-3 py-2">{{ t('category') }}</th>
            <th class="border px-3 py-2">{{ t('name') }}</th>
            <th class="border px-3 py-2">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="conference in filteredConferences" :key="conference.id" class="odd:bg-white even:bg-gray-100">
            <td class="border px-3 py-2">{{ conference.category ? conference.category.name : '-' }}</td>
            <td class="border px-3 py-2">{{ conference.name }}</td>
            <td class="border px-3 py-2 space-x-2">
              <inertia-link :href="route('admin.credit-conferences.edit', conference.id)" class="btn btn-sm btn-secondary">{{ t('edit') }}</inertia-link>
              <button @click="destroy(conference.id)" class="btn btn-sm btn-danger">{{ t('delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'

const { t } = useI18n()
const props = defineProps({
  conferences: Array,
  categories: Array
})

const selectedCategory = ref(0)

const filteredConferences = computed(() => {
  if (!selectedCategory.value || selectedCategory.value === 0) return props.conferences
  return props.conferences.filter(c => c.credit_category_id === selectedCategory.value)
})

const destroy = (id) => {
  if (confirm(t('delete_confirm'))) {
    router.delete(route('admin.credit-conferences.destroy', id))
  }
}

const filterConferences = () => {
  // optional: サーバ再取得する場合は router.get で
}
</script>


