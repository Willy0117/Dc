<template>
  <AppLayout :title="t('credit_categories')">
    <template #header>{{ editMode ? t('edit') : t('create') }}</template>

    <form @submit.prevent="submit" class="p-6 space-y-4">
      <div>
        <label class="block mb-1">{{ t('name') }}</label>
        <input v-model="form.name" type="text" class="input w-full" />
      </div>

      <div class="flex space-x-2">
        <button type="submit" class="btn btn-primary">{{ editMode ? t('update') : t('save') }}</button>
        <inertia-link :href="route('admin.credit-categories.index')" class="btn btn-secondary">{{ t('cancel') }}</inertia-link>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const props = defineProps({ category: Object || null })
const editMode = !!props.category

const form = reactive({
  name: props.category?.name || ''
})

const submit = () => {
  if (editMode) {
    router.put(route('admin.credit-categories.update', props.category.id), form)
  } else {
    router.post(route('admin.credit-categories.store'), form)
  }
}
</script>
