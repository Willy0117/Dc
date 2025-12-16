<template>
  <AppLayout>
    <template #header>{{ t('edit_device') }}</template>

    <div class="p-6">
      <div class="space-y-4">
      <form @submit.prevent="submitForm">
        <!-- Code -->
        <div>
          <label class="block mb-1">{{ t('code') }}</label>
          <input
            v-model="form.code"
            @input="form.code = toHalfWidth(form.code)"
            type="text"
            placeholder="Code"
            class="border rounded px-3 py-2 w-full"
          />
          <p v-if="errors.code" class="text-red-500 text-sm mt-1">{{ errors.code }}</p>
        </div>

        <!-- Name -->
        <div>
          <label class="block mb-1">{{ t('name') }}</label>
          <input v-model="form.name" type="text" class="border rounded px-3 py-2 w-full" />
          <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
        </div>

        <!-- process -->
       <div>
          <label class="block">{{ t('process') }}</label>
          <select v-model="form.process_id" class="mt-1 block w-full">
            <option v-for="p in processes" :key="p.id" :value="p.id">
              {{ p.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block">
            <span class="block mb-1">{{ t('measurement') }}</span>

            <div
              class="flex items-center border rounded px-3 h-10 bg-white cursor-pointer"
              @click="form.measurement = form.measurement ? 0 : 1"
            >
              <input
                type="checkbox"
                v-model="form.measurement"
                :true-value="1"
                :false-value="0"
                class="w-4 h-4"
              />
              <span class="ml-2 text-gray-700">
                {{ form.measurement ? t('yes') : t('no') }}
              </span>
            </div>
          </label>
        </div>

        <div>
          <label class="block">
            <span class="block mb-1">{{ t('disabled') }}</span>

            <div
              class="flex items-center border rounded px-3 h-10 bg-white cursor-pointer"
              @click="form.disabled = form.disabled ? 0 : 1"
            >
              <input
                type="checkbox"
                v-model="form.disabled"
                :true-value="1"
                :false-value="0"
                class="w-4 h-4"
              />
              <span class="ml-2 text-gray-700">
                {{ form.disabled ? t('enable') : t('disable') }}
              </span>
            </div>
          </label>
        </div>
        <!-- Tenant 選択 (Super Admin のみ) -->
        <div v-if="isSuperAdmin" class="mt-4">
          <label class="block mb-1">{{ t('tenant') }}</label>
          <select v-model="form.tenant_id" class="border rounded px-3 py-2 w-full">
            <option :value="null">{{ t('select_tenant') }}</option>
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
              {{ tenant.name }}
            </option>
          </select>
        </div>

        <!-- Display Order -->
        <div class="mb-4">
          <label class="block mb-1">{{ t('display_order') }}</label>
          <input
            v-model.number="form.display_order"
            type="number"
            class="border rounded px-3 py-2 w-full"
          />
        </div>

        <!-- Buttons -->
        <div class="flex space-x-2">
          <button
            type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          >
            {{ t('update') }}
          </button>
          <button
            type="button"
            @click="router.get(route('devices.index'), props.filters, { preserveState: true })"
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
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'

const props = defineProps({
  device: Object,
  tenants: Array,      // Super Admin のみ
  user: Object,
  processes: Array,
  filters: Object
})

const { t } = useI18n()
const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super admin')
)

const processes = props.processes

const form = reactive({
  code: props.device.code,
  name: props.device.name,
  process_id: props.device.process_id,
  measurement: props.device?.measurement ?? 0, // 0/1
  disabled: props.device?.disabled ?? 1,       // 0/1
  display_order: props.device.display_order,
  tenant_id: props.device?.tenant_id 
  ?? (isSuperAdmin.value ? null : props.user?.tenant_id ?? null)
})

const errors = reactive({
  code: '',
  name: '',
  process: '',
  measurement: '',
})

// リアルタイム重複チェック: code
watch(() => form.code, async (newCode) => {
  if (!newCode) { errors.code = ''; return }
  try {
    const response = await axios.post(
      route('devices.checkCode'),
      { code: newCode, id: props.device.id },
      {
        headers: {
          Accept: 'application/json',
        },
      }
    )
    errors.code = response.data.exists ? t('code_already_exists') : ''
  } catch (e) {
    console.error(e)
  }
})
// 全角→半角変換
const toHalfWidth = (str) => {
  if (!str) return ''
  return str.replace(/[！-～]/g, (s) => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
            .replace(/　/g, ' ') // 全角スペースを半角スペースに
}

const submitForm = () => {
  router.put(
    route('devices.update', props.device.id), // filters は付けない
    form,
    {
      preserveState: true,
      onError: (err) => Object.assign(errors, err),
      onSuccess: () => router.get(route('devices.index', props.filters)), // index の検索条件を保持して戻る
    }
  )
}

</script>