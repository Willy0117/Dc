<template>
  <AppLayout>
    <template #header>{{ t('add_tenant') }}</template>

    <div class="p-6">
      <div class="space-y-4">

        <div>
          <label class="block">{{ t('name') }}</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Name"
            class="border rounded px-3 py-2 w-full"
          />
          <div v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</div>
        </div>

        <div>
          <label class="block">{{ t('contact_email') }}</label>
          <input
            v-model="form.contact_email"
            type="email"
            placeholder="Email"
            class="border rounded px-3 py-2 w-full"
          />
          <div v-if="errors.contact_email" class="text-red-500 text-sm">{{ errors.contact_email }}</div>
        </div>

        <div>
          <label class="block">{{ t('contact_phone') }}</label>
          <input
            v-model="form.contact_phone"
            type="text"
            placeholder="Phone"
            class="border rounded px-3 py-2 w-full"
          />
          <div v-if="errors.contact_phone" class="text-red-500 text-sm">{{ errors.contact_phone }}</div>
        </div>

        <div>
          <label class="block">{{ t('address') }}</label>
          <input
            v-model="form.address"
            type="text"
            placeholder="Address"
            class="border rounded px-3 py-2 w-full"
          />
          <div v-if="errors.address" class="text-red-500 text-sm">{{ errors.address }}</div>
        </div>

        <div class="flex space-x-2">
          <button @click="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">{{ t('save') }}</button>
          <button
            @click="router.get(route('admin.tenants.index', props.filters), { preserveState: true })"
            class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
          >
            {{ t('cancel') }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Props に mode と tenant_id を追加
const props = defineProps({
  filters: Object,
  tenant: Object, // 編集 or コピー用
  mode: { type: String, default: '' },
  tenant_id: Number
})

const form = reactive({
  name: props.tenant?.name ?? '',
  contact_email: props.tenant?.contact_email ?? '',
  contact_phone: props.tenant?.contact_phone ?? '',
  address: props.tenant?.address ?? ''
})

// エラー管理
const errors = reactive({ name: '', contact_email: '', contact_phone: '', address: '' })

// コピー処理
onMounted(() => {
  if (props.mode === 'copy' && props.tenant_id) {
    const tenant = props.tenants.find(t => t.id === props.tenant_id)
    if (tenant) {
      form.name = tenant.name
      form.contact_email = tenant.contact_email
      form.contact_phone = tenant.contact_phone
      form.address = tenant.address
    }
  }
})

const submit = () => {
  router.post(route('admin.tenants.store'), form, {
    preserveState: true,
    onSuccess: () => router.get(route('admin.tenants.index', props.filters)),
    onError: (errs) => Object.assign(errors, errs)
  })
}
</script>
