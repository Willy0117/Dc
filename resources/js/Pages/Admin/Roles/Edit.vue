<template>
  <AppLayout>
    <template #header>
      {{ role ? t('roles.edit') : t('roles.create') }}
    </template>

    <div class="p-6">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Role Name -->
        <div>
          <label class="block mb-1 font-medium">{{ t('roles.name') }}</label>
          <input
            v-model="form.name"
            type="text"
            class="border rounded px-3 py-2 w-full"
            placeholder="Role Name"
          />
          <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
        </div>
        <div class="space-y-2">
            <Label for="guard_name">対象</Label>

            <Select v-model="form.guard_name">
                <SelectTrigger>
                    <SelectValue placeholder="対象を選択" />
                </SelectTrigger>

                <SelectContent class="bg-white border shadow-md">
                    <SelectItem value="admin">
                        管理者
                    </SelectItem>

                    <SelectItem value="web">
                        会員
                    </SelectItem>
                </SelectContent>
            </Select>

            <p
                v-if="errors.guard_name"
                class="text-red-500 text-sm mt-1"
            >
                {{ errors.guard_name }}
            </p>
        </div>
        <!-- Permissions MultiSelect -->
        <div>
          <label class="block mb-1 font-medium">{{ t('permissions.permission') }}</label>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-96 overflow-y-auto border rounded p-2">
            <div v-for="permission in permissions" :key="permission.id" class="mb-2">
              <label :for="'perm-' + permission.id" class="inline-flex items-center space-x-2">
                <input
                  type="checkbox"
                  :id="'perm-' + permission.id"
                  :value="permission.id"
                  v-model="form.permissions"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                />
                <span class="text-gray-700">
                  {{ permission.name }} {{ permission.tenant_label }}
                </span>
              </label>
            </div>
         </div>
          <p v-if="errors.permissions" class="text-red-500 text-sm mt-1">{{ errors.permissions }}</p>
        </div>

        <!-- Buttons -->
        <div class="flex space-x-2 mt-4">
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          >
            {{ role ? t('update') : t('create') }}
          </button>
          <button
            type="button"
            @click="router.get(route('admin.roles.index', filters), { preserveState: true })"
            class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
          >
            {{ t('cancel') }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { useI18n } from 'vue-i18n'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Props
const props = defineProps({
  role: Object,           // null = create, object = edit
  permissions: Array,     // {id, name, tenant_id, tenant_name?}
  filters: Object,        // Index画面検索条件
})

// i18n
const { t } = useI18n()

// 初期フォーム値
const form = reactive({
  name: props.role?.name || '',
  guard_name: props.role?.guard_name || '',
  permissions: props.role?.permissions?.map(p => p.id) || [],
})

// エラー管理
const errors = reactive({})

// 送信処理
const submitForm = () => {
  const method = props.role ? 'put' : 'post'
  const url = props.role
    ? route('admin.roles.update', props.role.id)
    : route('admin.roles.store')

  router[method](url, form, {
    preserveState: true,
    onError: err => Object.assign(errors, err),
    onSuccess: () => router.get(route('admin.roles.index', props.filters)),
  })
}
</script>

