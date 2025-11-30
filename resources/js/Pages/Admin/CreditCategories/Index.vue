<template>
  <AppLayout :title="t('credit_categories')">
    <template #header>{{ t('credit_categories') }}</template>

    <div class="p-6">
      <inertia-link :href="route('admin.credit-categories.create')" class="btn btn-primary mb-4">
        {{ t('create') }}
      </inertia-link>

      <table class="table-auto w-full border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="border px-3 py-2">{{ t('name') }}</th>
            <th class="border px-3 py-2">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id" class="odd:bg-white even:bg-gray-100">
            <td class="border px-3 py-2">{{ category.name }}</td>
            <td class="border px-3 py-2 space-x-2">
              <inertia-link :href="route('admin.credit-categories.edit', category.id)" class="btn btn-sm btn-secondary">
                {{ t('edit') }}
              </inertia-link>
              <button @click="destroy(category.id)" class="btn btn-sm btn-danger">{{ t('delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const props = defineProps({ categories: Array })

const destroy = (id) => {
  if (confirm(t('delete_confirm'))) {
    router.delete(route('admin.credit-categories.destroy', id))
  }
}
</script>

