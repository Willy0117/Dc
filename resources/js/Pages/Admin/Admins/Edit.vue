<template>
  <AppLayout>
    <template #header>
      {{ admin ? t('admins.edit') : t('admins.create') }}
    </template>

    <div class="p-6 max-w-2xl mx-auto bg-white rounded shadow">
      <form @submit.prevent="submit" class="space-y-4">
        <!-- 名前 -->
        <div>
          <InputLabel for="name" :value="t('name')" />
          <TextInput
            id="name"
            v-model="form.name"
            type="text"
            class="mt-1 block w-full"
            autofocus
          />
          <InputError :message="form.errors.name" class="mt-2" />
        </div>

        <!-- メール -->
        <div>
          <InputLabel for="email" :value="t('email')" />
          <TextInput
            id="email"
            v-model="form.email"
            type="email"
            class="mt-1 block w-full"
          />
          <InputError :message="form.errors.email" class="mt-2" />
        </div>

        <!-- パスワード -->
        <div>
          <InputLabel for="password" :value="t('password')" />
          <TextInput
            id="password"
            v-model="form.password"
            type="password"
            class="mt-1 block w-full"
          />
          <InputError :message="form.errors.password" class="mt-2" />
        </div>

        <!-- 確認 -->
        <div>
          <InputLabel for="password_confirmation" :value="t('users.confirm_password')" />
          <TextInput
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="mt-1 block w-full"
          />
        </div>

        <!-- Tenant -->
        <div v-if="isSuperAdmin">
          <InputLabel for="tenant_id" :value="t('tenant')" />
          <select
            id="tenant_id"
            v-model="form.tenant_id"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          >
            <option value="" >{{ t('select_tenant') }}</option>
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
              {{ tenant.name }}
            </option>
          </select>
          <InputError :message="form.errors.tenant_id" class="mt-2" />
        </div>

        <!-- Role -->
        <div>
          <InputLabel for="role_id" :value="t('role')" />
          <select
            id="role_id"
            v-model="form.role_id"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          >
            <option value="" >{{ t('select_role') }}</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.name }} - {{ role.tenant_name }}
            </option>
          </select>
          <InputError :message="form.errors.role_id" class="mt-2" />
        </div>

        <!-- 保存 -->
        <div class="flex justify-end">
          <PrimaryButton :disabled="form.processing">
            {{ admin ? t('admins.update') : t('admins.create') }}
          </PrimaryButton>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { useForm,usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import Autocomplete from '@/Components/OpenCartAutocomplete.vue'
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const { t } = useI18n()

const page = usePage()

const inertiaProps = page.props

const props = defineProps({
  admin: { type: Object, default: () => ({}) },
  roles: { type: Array, default: () => [] },
  tenants: { type: Array, default: () => [] },
  selected_role: { type: Number, default: null }
})

const user = inertiaProps.auth.admin ?? inertiaProps.auth.user

console.log('admin',inertiaProps.auth.admin)

console.log('user',inertiaProps.auth.user)

console.log(user)

// Super Admin 判定
const isSuperAdmin =
  user?.roles?.some(r => ['super_admin', 'admin'].includes(r))

const form = useForm({
  name: props.admin?.name || '',
  email: props.admin?.email || '',
  password: '',
  password_confirmation: '',
  role_id: props.selected_role || null,
  tenant_id: props.admin?.tenant_id || null,
})

const submit = () => {
  console.log('送信データ:', form);
  if (props.admin?.id) {
    form.put(route('admin.admins.update', props.admin.id))
  } else {
    form.post(route('admin.admins.store'))
  }
}

</script>


