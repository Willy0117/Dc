<template>
  <AppLayout>
    <template #header>
      {{ permission?.id ? t('permissions.edit') : t('permissions.create') }}
    </template>

    <div class="p-6 max-w-2xl mx-auto">
      <div class="bg-white border rounded-lg p-6 space-y-5">

        <!-- Permission Name -->
        <div class="space-y-1.5">
          <Label for="name">{{ t('name') }}</Label>
          <Input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="Permission Name"
            autofocus
          />
          <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
        </div>

        <!-- Tenant（SuperAdminのみ） -->
        <div v-if="tenants.length > 0" class="space-y-1.5">
          <Label for="tenant_id">{{ t('tenant') }}</Label>
          <Select v-model="form.tenant_id">
            <SelectTrigger id="tenant_id">
              <SelectValue :placeholder="t('select_tenant')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem :value="null">{{ t('select_tenant') }}</SelectItem>
              <SelectItem v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                {{ tenant.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="errors.tenant_id" class="text-sm text-destructive">{{ errors.tenant_id }}</p>
        </div>

        <!-- 対象（guard_name） -->
        <div class="space-y-1.5">
          <Label for="guard_name">対象</Label>
          <Select v-model="form.guard_name">
            <SelectTrigger id="guard_name">
              <SelectValue placeholder="対象を選択" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="admin">管理者</SelectItem>
              <SelectItem value="web">会員</SelectItem>
            </SelectContent>
          </Select>
          <p v-if="errors.guard_name" class="text-sm text-destructive">{{ errors.guard_name }}</p>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" as-child>
            <Link :href="route('admin.permissions.index', filters)">{{ t('cancel') }}</Link>
          </Button>
          <Button @click="submitForm">
            {{ permission?.id ? t('update') : t('create') }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

import AppLayout from '@/Layouts/Admin/AppLayout.vue'

import { Button } from '@/components/ui/button'
import { Input }  from '@/components/ui/input'
import { Label }  from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

// ──────────────────────────────────────────
// Props
// ──────────────────────────────────────────
const props = defineProps({
  permission: { type: Object, default: null },
  tenants:    { type: Array,  default: () => [] },
  filters:    { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { props: pageProps } = usePage()

// ──────────────────────────────────────────
// SuperAdmin 判定
// ──────────────────────────────────────────
const user = pageProps.auth?.admin ?? pageProps.auth?.user
const isSuperAdmin = computed(() =>
  user?.roles?.some(r => r.toLowerCase() === 'super_admin')
)

// ──────────────────────────────────────────
// フォーム
// ──────────────────────────────────────────
const form = reactive({
  name:       props.permission?.name       ?? '',
  guard_name: props.permission?.guard_name ?? '',
  tenant_id:  props.permission?.tenant_id  ?? (isSuperAdmin.value ? null : user?.tenant_id ?? null),
})

const errors = reactive({})

// ──────────────────────────────────────────
// 送信
// ──────────────────────────────────────────
const submitForm = () => {
  const onError   = (err) => Object.assign(errors, err)
  const onSuccess = ()    => router.get(route('admin.permissions.index', props.filters))

  if (props.permission?.id) {
    router.put(route('admin.permissions.update', props.permission.id), form, {
      preserveState: true, onError, onSuccess,
    })
  } else {
    router.post(route('admin.permissions.store'), form, {
      preserveState: true, onError, onSuccess,
    })
  }
}
</script>