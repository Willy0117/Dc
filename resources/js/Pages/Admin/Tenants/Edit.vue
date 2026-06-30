<template>
  <AppLayout>
    <template #header>
      {{ tenant?.id ? t('edit_tenant') : t('tenants.add_tenant') }}
    </template>

    <div class="p-6 max-w-2xl mx-auto">
      <div class="bg-white border rounded-lg p-6 space-y-5">

        <!-- Name -->
        <div class="space-y-1.5">
          <Label for="name">{{ t('name') }}</Label>
          <Input
            id="name"
            v-model="form.name"
            type="text"
            autofocus
          />
          <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
        </div>

        <!-- Contact Email -->
        <div class="space-y-1.5">
          <Label for="contact_email">{{ t('contact_email') }}</Label>
          <Input
            id="contact_email"
            v-model="form.contact_email"
            type="email"
          />
          <p v-if="errors.contact_email" class="text-sm text-destructive">{{ errors.contact_email }}</p>
        </div>

        <!-- Contact Phone -->
        <div class="space-y-1.5">
          <Label for="contact_phone">{{ t('contact_phone') }}</Label>
          <Input
            id="contact_phone"
            v-model="form.contact_phone"
            type="text"
          />
          <p v-if="errors.contact_phone" class="text-sm text-destructive">{{ errors.contact_phone }}</p>
        </div>

        <!-- Address -->
        <div class="space-y-1.5">
          <Label for="address">{{ t('address') }}</Label>
          <Input
            id="address"
            v-model="form.address"
            type="text"
          />
          <p v-if="errors.address" class="text-sm text-destructive">{{ errors.address }}</p>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" as-child>
            <Link :href="route('admin.tenants.index', filters)">{{ t('cancel') }}</Link>
          </Button>
          <Button @click="submitForm">
            {{ tenant?.id ? t('update') : t('create') }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

import AppLayout from '@/Layouts/Admin/AppLayout.vue'

import { Button } from '@/components/ui/button'
import { Input }  from '@/components/ui/input'
import { Label }  from '@/components/ui/label'

// ──────────────────────────────────────────
// Props
// ──────────────────────────────────────────
const props = defineProps({
  tenant:  { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

// ──────────────────────────────────────────
// フォーム
// ──────────────────────────────────────────
const form = reactive({
  name:          props.tenant?.name          ?? '',
  contact_email: props.tenant?.contact_email ?? '',
  contact_phone: props.tenant?.contact_phone ?? '',
  address:       props.tenant?.address       ?? '',
})

const errors = reactive({})

// ──────────────────────────────────────────
// 送信
// ──────────────────────────────────────────
const submitForm = () => {
  const onError   = (err) => Object.assign(errors, err)
  const onSuccess = ()    => router.get(route('admin.tenants.index', props.filters))

  if (props.tenant?.id) {
    router.put(route('admin.tenants.update', props.tenant.id), form, {
      preserveState: true, onError, onSuccess,
    })
  } else {
    router.post(route('admin.tenants.store'), form, {
      preserveState: true, onError, onSuccess,
    })
  }
}
</script>