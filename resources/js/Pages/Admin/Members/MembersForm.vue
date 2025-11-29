<template>
  <AppLayout :title="member ? t('edit_member') : t('add_member')">
    <template #header>
      {{ member ? t('edit_member') : t('add_member') }}
    </template>

    <form @submit.prevent="submit">
      <div class="space-y-3 p-4">
        <input v-model="form.login_id" type="text" :placeholder="t('login_id')" class="border rounded px-3 py-2 w-full"/>
        <input v-model="form.name" type="text" :placeholder="t('name')" class="border rounded px-3 py-2 w-full"/>
        <input v-model="form.phone" type="text" :placeholder="t('phone')" class="border rounded px-3 py-2 w-full"/>
        <textarea v-model="form.address" :placeholder="t('address')" class="border rounded px-3 py-2 w-full"></textarea>
        <select v-model="form.status" class="border rounded px-3 py-2 w-full">
          <option value="provisional">{{ t('provisional') }}</option>
          <option value="regular">{{ t('regular') }}</option>
          <option value="suspended">{{ t('suspended') }}</option>
          <option value="expelled">{{ t('expelled') }}</option>
        </select>
      </div>

      <div class="p-4 flex justify-end gap-2">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">{{ t('save') }}</button>
        <Link :href="route('admin.members.index')" class="bg-gray-300 px-4 py-2 rounded">{{ t('cancel') }}</Link>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  member: Object // null なら新規
})

const { t } = useI18n()

// 編集 or 新規でフォーム初期化
const form = reactive({
  login_id: props.member?.login_id || '',
  name: props.member?.name || '',
  phone: props.member?.phone || '',
  address: props.member?.address || '',
  status: props.member?.status || 'provisional'
})

// submit メソッド
const submit = () => {
  if(props.member) {
    router.put(route('admin.members.update', props.member.id), form)
  } else {
    router.post(route('admin.members.store'), form)
  }
}
</script>
