<template>
  <AppLayout>
    <template #header>
      {{ t('assign_permission') }}
    </template>

    <div class="p-6">
      <div class="space-y-4">
        <form @submit.prevent="submitForm">
          <!-- Permission 名 -->
          <div>
            <label class="block mb-1">{{ t('permission_name') }}</label>
            <input
              type="text"
              v-model="permission.name"
              disabled
              class="border rounded px-3 py-2 w-full bg-gray-100 text-gray-600"
            />
          </div>

          <!-- ユーザー選択 -->
          <div>
            <label class="block mb-1">{{ t('select_user') }}</label>
            <select v-model="form.user_id" class="border rounded px-3 py-2 w-full">
              <option value="">{{ t('choose_user') }}</option>
              <option
                v-for="user in users"
                :key="user.id"
                :value="user.id"
              >
                {{ user.name }} ({{ user.email }})
              </option>
            </select>
            <p v-if="errors.user_id" class="text-red-500 text-sm mt-1">{{ errors.user_id }}</p>
          </div>

          <!-- Buttons -->
          <div class="flex space-x-2 mt-6">
            <button
              type="submit"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
              {{ t('assign') }}
            </button>
            <button
              @click="router.get(route('admin.permissions.index', filters), { preserveState: true })"
              type="button"
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
  permission: Object,
  users: Array,
  filters: Object
})

const { t } = useI18n()

// フォーム定義
const form = reactive({
  user_id: ''
})

// エラー管理
const errors = reactive({
  user_id: ''
})

// 割り当て処理
const submitForm = () => {
  router.post(route('admin.permissions.assign.store', props.permission.id), form, {
    preserveState: true,
    onError: (err) => Object.assign(errors, err),
    onSuccess: () => router.get(route('admin.permissions.index', props.filters))
  })
}
</script>
