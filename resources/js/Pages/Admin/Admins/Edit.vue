<template>
  <AppLayout>
    <template #header>
      {{ admin?.id ? t('admins.edit') : t('admins.create') }}
    </template>

    <div class="p-6 max-w-2xl mx-auto">
      <div class="bg-white border rounded-lg p-6 space-y-5">
        <!-- 名前 -->
        <div class="space-y-1.5">
          <Label for="name">{{ t('name') }}</Label>
          <Input
            id="name"
            v-model="form.name"
            type="text"
            autofocus
          />
          <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
        </div>

        <!-- メール -->
        <div class="space-y-1.5">
          <Label for="email">{{ t('email') }}</Label>
          <Input
            id="email"
            v-model="form.email"
            type="email"
          />
          <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
        </div>

        <!-- パスワード -->
        <div class="space-y-1.5">
          <Label for="password">{{ t('password') }}</Label>
          <Input
            id="password"
            v-model="form.password"
            type="password"
          />
          <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
        </div>

        <!-- パスワード確認 -->
        <div class="space-y-1.5">
          <Label for="password_confirmation">{{ t('users.confirm_password') }}</Label>
          <Input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
          />
        </div>

        <!-- Tenant（SuperAdminのみ） -->
        <div v-if="isSuperAdmin" class="space-y-1.5">
          <Label for="tenant_id">{{ t('tenant') }}</Label>
          <Select v-model="form.tenant_id">
            <SelectTrigger id="tenant_id">
              <SelectValue :placeholder="t('select_tenant')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="none">{{ t('none') }}</SelectItem>
              <SelectItem v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                {{ tenant.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="form.errors.tenant_id" class="text-sm text-destructive">{{ form.errors.tenant_id }}</p>
        </div>

        <!-- Role -->
        <div class="space-y-1.5">
          <Label for="role_id">{{ t('role') }}</Label>
          <Select v-model="form.role_id">
            <SelectTrigger id="role_id">
              <SelectValue :placeholder="t('select_role')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="role in filteredRoles" :key="role.id" :value="role.id">
                {{ role.name }} - {{ role.tenant_name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="form.errors.role_id" class="text-sm text-destructive">{{ form.errors.role_id }}</p>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" as-child>
            <Link :href="route('admin.admins.index')">{{ t('cancel') }}</Link>
          </Button>
          <Button :disabled="form.processing" @click="submit">
            <Loader2 v-if="form.processing" class="w-3.5 h-3.5 mr-1 animate-spin" />
            {{ admin?.id ? t('admins.update') : t('admins.create') }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, watch } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Loader2 } from 'lucide-vue-next'

import AppLayout from '@/Layouts/Admin/AppLayout.vue'

import { Button }   from '@/components/ui/button'
import { Input }    from '@/components/ui/input'
import { Label }    from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  admin:         { type: Object, default: () => ({}) },
  roles:         { type: Array,  default: () => [] },
  tenants:       { type: Array,  default: () => [] },
  selected_role: { type: Number, default: null },
})

const { t } = useI18n()
const { props: pageProps } = usePage()

const user = pageProps.auth?.admin ?? pageProps.auth?.user
const isSuperAdmin = computed(() =>
  user?.roles?.some(r => ['super_admin', 'admin'].includes(r))
)

const form = useForm({
  name:                  props.admin?.name      ?? '',
  email:                 props.admin?.email     ?? '',
  password:              '',
  password_confirmation: '',
  role_id:               props.selected_role    ?? null,
  tenant_id:             props.admin?.tenant_id ?? 'none',
})

// tenant_idに応じてroleを絞り込む(SuperAdmin以外はtenant選択自体がないので全roleのまま)
const filteredRoles = computed(() => {
  if (!isSuperAdmin.value) return props.roles

  const tid = form.tenant_id === 'none' ? null : form.tenant_id
  return props.roles.filter(r => r.tenant_id === tid)
})

// tenant_idが変わったら、role_idが新しいtenantに属していなければリセット(SuperAdminのみ対象)
watch(() => form.tenant_id, () => {
  if (!isSuperAdmin.value) return

  const tid = form.tenant_id === 'none' ? null : form.tenant_id
  const stillValid = props.roles.some(r => r.id === form.role_id && r.tenant_id === tid)
  if (!stillValid) {
    form.role_id = null
  }
})

const submit = () => {
  form.transform(data => ({
    ...data,
    tenant_id: data.tenant_id === 'none' ? null : data.tenant_id,
  }))

  if (props.admin?.id) {
    form.put(route('admin.admins.update', props.admin.id))
  } else {
    form.post(route('admin.admins.store'))
  }
}
</script>
