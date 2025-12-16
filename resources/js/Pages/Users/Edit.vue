<template>
  <AppLayout>
    <template #header>
      {{ user ? t('edit_user') : t('create_user') }}
    </template>

    <div class="p-6 max-w-2xl mx-auto bg-white rounded shadow">
      <form @submit.prevent="submit" class="space-y-4">
        <!-- 名前 -->
        <div>
          <label class="block mb-1 font-medium">{{ t('name') }}</label>
          <input v-model="form.name" type="text" class="border rounded px-3 py-2 w-full" />
          <div v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</div>
        </div>

        <!-- メール -->
        <div>
          <label class="block mb-1 font-medium">{{ t('email') }}</label>
          <input v-model="form.email" type="email" class="border rounded px-3 py-2 w-full" />
          <div v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</div>
        </div>

        <!-- パスワード -->
        <div>
          <label class="block mb-1 font-medium">{{ t('password') }}</label>
          <input v-model="form.password" type="password" class="border rounded px-3 py-2 w-full" />
          <div v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</div>
        </div>

        <div>
          <label class="block mb-1 font-medium">{{ t('confirm password') }}</label>
          <input v-model="form.password_confirmation" type="password" class="border rounded px-3 py-2 w-full" />
        </div>

        <!-- Tenant（SuperAdminのみ） -->
        <div v-if="isSuperAdmin">
          <label class="block mb-1 font-medium">{{ t('tenant') }}</label>
          <select v-model="form.tenant_id" class="border rounded px-3 py-2 w-full">
            <option :value="null" disabled>{{ t('select_tenant') }}</option>
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
              {{ tenant.name }}
            </option>
          </select>
          <div v-if="errors.tenant_id" class="text-red-500 text-sm">{{ errors.tenant_id }}</div>
        </div>

        <!-- Role選択 -->
        <div>
          <label class="block mb-1 font-medium">{{ t('role') }}</label>
          <select v-model="form.role_id" class="border rounded px-3 py-2 w-full">
            <option :value="null" disabled>{{ t('select_role') }}</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.name }} - {{ role.tenant_name }}
            </option>
          </select>
          <div v-if="errors.role_id" class="text-red-500 text-sm">{{ errors.role_id }}</div>
        </div>

        <!-- 保存ボタン -->
        <div class="flex justify-between items-center">
          <!-- 左：キャンセル -->
          <button
            type="button"
            @click="router.get(route('users.index', persistQuery()))"
            class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
          >
            {{ t('cancel') }}
          </button>

          <!-- 右：作成/更新 -->
          <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
          >
            {{ user ? t('update') : t('create') }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  auth: { type: Object, default: () => ({}) },
  user: { type: Object, default: () => ({}) },
  roles: { type: Array, default: () => [] },
  tenants: { type: Array, default: () => [] },
  selected_role: { type: Number, default: null },
  filters: { type: Object, default: () => ({}) },
})
// Super Admin 判定
const isSuperAdmin = computed(() => {
  const roles = props.auth.user?.roles || [];
  return roles.some(role => role.name.toLowerCase() === 'super admin');
});

const persistQuery = () => ({
  code: props.filters.code ?? '',
  name: props.filters.name ?? '',
  email: props.filters.email ?? '',
  tenant_id: props.filters.tenant_id ?? '',
  per_page: props.filters.per_page ?? 20,
  sort_by: props.filters.sort_by ?? 'id',
  sort_dir: props.filters.sort_dir ?? 'asc',
  page: props.filters.page ?? 1,
})

const form = useForm({
  name: props.user?.name || '',
  email: props.user?.email || '',
  password: '',
  password_confirmation: '',
  role_id: props.selected_role || null,
  tenant_id: props.user?.tenant_id || null
})

const errors = form.errors

const submit = () => {
  const method = props.user?.id ? 'put' : 'post';
  const url = props.user?.id ? route('users.update', props.user.id) : route('users.store');

  form[method](url, {
    onSuccess: () => {
      router.get(route('users.index'), props.filters, { preserveState: true });
    },
    onError: (e) => console.log(e)
  });
}

</script>


