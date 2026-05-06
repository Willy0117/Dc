<template>
  <AppLayout>
    <template #header>{{ t('edit_tenant') }}</template>

    <div class="p-6">
      <div class="space-y-4">
        <form @submit.prevent="submitForm">

          <!-- Name -->
          <div>
            <label class="block mb-1">{{ t('name') }}</label>
            <input v-model="form.name" type="text" class="border rounded px-3 py-2 w-full" />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
          </div>

          <!-- Contact Email -->
          <div>
            <label class="block mb-1">{{ t('contact_email') }}</label>
            <input v-model="form.contact_email" type="email" class="border rounded px-3 py-2 w-full" />
            <p v-if="errors.contact_email" class="text-red-500 text-sm mt-1">{{ errors.contact_email }}</p>
          </div>

          <!-- Contact Phone -->
          <div>
            <label class="block mb-1">{{ t('contact_phone') }}</label>
            <input v-model="form.contact_phone" type="text" class="border rounded px-3 py-2 w-full" />
            <p v-if="errors.contact_phone" class="text-red-500 text-sm mt-1">{{ errors.contact_phone }}</p>
          </div>

          <!-- Address -->
          <div>
            <label class="block mb-1">{{ t('address') }}</label>
            <input v-model="form.address" type="text" class="border rounded px-3 py-2 w-full" />
            <p v-if="errors.address" class="text-red-500 text-sm mt-1">{{ errors.address }}</p>
          </div>

          <!-- Buttons -->
          <div class="flex space-x-2 mt-4">
            <button
              type="submit"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
              {{ t('update') }}
            </button>
            <button
              @click="router.get(route('admin.tenants.index'), props.filters, { preserveState: true })"
              class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
            >
              {{ t('cancel') }}
            </button>
          </div>

        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  tenant: Object,
  filters: Object
})

const { t } = useI18n()

const form = reactive({
  name: props.tenant.name,
  contact_email: props.tenant.contact_email ?? '',
  contact_phone: props.tenant.contact_phone ?? '',
  address: props.tenant.address ?? ''
})

const errors = reactive({
  name: '',
  contact_email: '',
  contact_phone: '',
  address: ''
})

const submitForm = () => {
  router.put(
    route('admin.tenants.update', props.tenant.id),
    form,
    {
      preserveState: true,
      onError: (err) => Object.assign(errors, err),
      onSuccess: () => router.get(route('admin.tenants.index', props.filters))
    }
  )
}
</script>
