<template>
  <AppLayout>
    <template #header>
      {{ permission ? t('permissions.edit') : t('permissions.create') }}
    </template>

    <div class="p-6">
      <div class="space-y-4">
        <form @submit.prevent="submitForm">
          <!-- Permission Name -->
          <div>
            <InputLabel :value="t('name')" />
            <TextInput
              v-model="form.name"
              type="text"
              placeholder="Permission Name"
              class="border rounded px-3 py-2 w-full"
            />
            <InputError :message="form.errors?.name" class="mt-2" />
          </div>

          <!-- Tenant 選択 (Super Admin のみ) -->
          <div v-if="tenants.length>0" class="mt-4">
            <label class="block mb-1">{{ t('tenant') }}</label>
            <select v-model="form.tenant_id" class="border rounded px-3 py-2 w-full">
              <option :value="null">{{ t('select_tenant') }}</option>
              <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                {{ tenant.name }}
              </option>
            </select>
          </div>

          <!-- Buttons -->
          <div class="flex space-x-2 mt-6">
            <PrimaryButton
              type="submit"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
              {{ permission ? t('update') : t('create') }}
            </PrimaryButton>
            <SecondaryButton
              @click="router.get(route('admin.permissions.index', filters), { preserveState: true })"
              type="button"
              class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
            >
              {{ t('cancel') }}
            </SecondaryButton>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

import { router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  permission: Object,  // null = 新規作成, オブジェクト = 編集
  tenants: Array,      // Super Admin のみ
  user: Object,        // 現在のログインユーザー
  filters: Object      // Index画面の検索条件
})

const { t } = useI18n()

console.log(props)

// Super Admin 判定
const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super_admin')
)

// フォーム初期値
const form = reactive({
  name: props.permission ? props.permission.name : '',
  tenant_id: props.permission
    ? props.permission.tenant_id
    : (isSuperAdmin.value ? null : props.user?.tenant_id ?? null)
})

// エラー管理
//const errors = reactive({
//  name: ''
//})

// 送信処理
const submitForm = () => {
  if (props.permission) {
    // 編集
    router.put(route('admin.permissions.update', props.permission.id), form, {
      preserveState: true,
      onError: (err) => Object.assign(errors, err),
      onSuccess: () => router.get(route('admin.permissions.index', props.filters))
    })
  } else {
    // 新規作成
    router.post(route('admin.permissions.store'), form, {
      preserveState: true,
      onError: (err) => Object.assign(errors, err),
      onSuccess: () => router.get(route('admin.permissions.index', props.filters))
    })
  }
}
</script>
