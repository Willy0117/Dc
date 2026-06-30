<template>
  <AppLayout>
    <template #header>
      {{ role?.id ? t('roles.edit') : t('roles.create') }}
    </template>

    <div class="p-6 max-w-2xl mx-auto">
      <div class="bg-white border rounded-lg p-6 space-y-5">

        <!-- Role Name -->
        <div class="space-y-1.5">
          <Label for="name">{{ t('roles.name') }}</Label>
          <Input
            id="name"
            v-model="form.name"
            type="text"
            placeholder="Role Name"
            autofocus
          />
          <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
        </div>
        <!-- tenant_id（SuperAdminのみ） -->
        <div v-if="tenants.length > 0" class="space-y-1.5">
          <Label for="tenant_id">{{ t('tenant') }}</Label>
          <Select v-model="form.tenant_id">
            <SelectTrigger id="tenant_id">
              <SelectValue :placeholder="t('select_tenant')" />
            </SelectTrigger>
            <SelectContent>
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

        <!-- Permissions -->
        <div class="space-y-1.5">
          <Label>{{ t('permissions.permission') }}</Label>
          <div class="border rounded-lg p-3 max-h-96 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
              <label
                v-for="permission in permissions"
                :key="permission.id"
                :for="'perm-' + permission.id"
                class="flex items-center gap-2 cursor-pointer rounded px-2 py-1.5 hover:bg-muted/50 transition-colors"
              >
                <Checkbox
                  :id="'perm-' + permission.id"
                  :model-value="form.permissions.includes(permission.id)"
                  @update:model-value="(checked) => togglePermission(permission.id, checked)"
                />
                <span class="text-sm">{{ permission.name }} {{ permission.tenant_label }}</span>
              </label>
            </div>
          </div>
          <p v-if="errors.permissions" class="text-sm text-destructive">{{ errors.permissions }}</p>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" as-child>
            <Link :href="route('admin.roles.index', filters)">{{ t('cancel') }}</Link>
          </Button>
          <Button @click="submitForm">
            {{ role?.id ? t('update') : t('create') }}
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

import { Button }   from '@/components/ui/button'
import { Input }    from '@/components/ui/input'
import { Label }    from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

// ──────────────────────────────────────────
// Props
// ──────────────────────────────────────────
const props = defineProps({
  role:        { type: Object, default: null },
  permissions: { type: Array,  default: () => [] },
  tenants:     { type: Array,  default: () => [] }, 
  filters:     { type: Object, default: () => ({}) },
})

const { t } = useI18n()

// ──────────────────────────────────────────
// フォーム
// ──────────────────────────────────────────
const form = reactive({
  name:        props.role?.name                        ?? '',
  guard_name:  props.role?.guard_name                  ?? '',
  permissions: props.role?.permissions?.map(p => p.id) ?? [],
  tenant_id:   props.role?.tenant_id ?? '',
})

const errors = reactive({})

const togglePermission = (id, checked) => {
  if (checked) {
    if (!form.permissions.includes(id)) form.permissions.push(id)
  } else {
    form.permissions = form.permissions.filter(i => i !== id)
  }
}

// ──────────────────────────────────────────
// 送信
// ──────────────────────────────────────────
const submitForm = () => {
  const method  = props.role?.id ? 'put' : 'post'
  const url     = props.role?.id
    ? route('admin.roles.update', props.role.id)
    : route('admin.roles.store')

  router[method](url, form, {
    preserveState: true,
    onError:   (err) => Object.assign(errors, err),
    onSuccess: ()    => router.get(route('admin.roles.index', props.filters)),
  })
}
</script>