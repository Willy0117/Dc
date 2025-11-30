<template>
  <AppLayout :title="editMode ? t('edit_credit_conference') : t('create_credit_conference')">
    <template #header>
      {{ editMode ? t('edit_credit_conference') : t('create_credit_conference') }}
    </template>

    <form @submit.prevent="submit" class="p-6 space-y-4">
      <!-- カテゴリー選択 -->
      <div>
        <label class="block mb-1">{{ t('credit_category') }}</label>
        <select v-model="form.credit_category_id" class="input w-full">
          <option :value="0">{{ t('select_category') }}</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
        </select>
      </div>

      <!-- 会議名 -->
      <div>
        <label class="block mb-1">{{ t('name') }}</label>
        <input v-model="form.name" type="text" class="input w-full" />
      </div>

      <div class="flex space-x-2">
        <button type="submit" class="btn btn-primary">{{ editMode ? t('update') : t('save') }}</button>
        <inertia-link :href="route('admin.credit-conferences.index')" class="btn btn-secondary">{{ t('cancel') }}</inertia-link>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'

const { t } = useI18n()
const props = defineProps({
  conference: Object || null,
  categories: Array
})

const editMode = !!props.conference

const form = reactive({
  name: props.conference?.name || '',
  credit_category_id: props.conference?.credit_category_id || 0
})

const submit = () => {
  if (form.credit_category_id === 0) {
    alert(t('please_select_category'))
    return
  }

  if (editMode) {
    router.put(route('admin.credit-conferences.update', props.conference.id), form)
  } else {
    router.post(route('admin.credit-conferences.store'), form)
  }
}
</script>

