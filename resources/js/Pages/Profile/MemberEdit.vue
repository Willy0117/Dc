<template>
  <AppLayout :title="t('profile.edit_member')">
    <template #header>{{ t('edit_member') }}</template>

    <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-lg mt-6">

      <form @submit.prevent="submit" class="space-y-4">
        <!-- 会員コード -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">{{ t('member_code') }}</label>
          <input v-model="form.member_code" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        </div>

        <!-- 氏名 -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">{{ t('name') }}</label>
          <input v-model="form.name" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        </div>

        <!-- 郵便番号 -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">{{ t('postal_code') }}</label>
          <input v-model="form.postal_code" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        </div>

        <!-- 住所1 -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">{{ t('address1') }}</label>
          <input v-model="form.address1" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        </div>

        <!-- 住所2 -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">{{ t('address2') }}</label>
          <input v-model="form.address2" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        </div>

        <!-- 電話 -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">{{ t('phone') }}</label>
          <input v-model="form.phone" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        </div>

        <!-- fax-->
        <div>
          <label class="block text-gray-700 font-medium mb-1">{{ t('fax') }}</label>
          <input v-model="form.fax" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        </div>

        <!-- 更新ボタン -->
        <div class="flex justify-end mt-4">
          <button type="submit"
                  class="bg-blue-500 text-white px-5 py-2 rounded hover:bg-blue-600">{{ t('update') }}</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

const props = defineProps({
  member: Object
})

const form = reactive({
  member_code: props.member?.member_code || '',
  name: props.member?.name || '',
  postal_code: props.member?.postal_code || '',
  address1: props.member?.address1 || '',
  address2: props.member?.address2 || '',
  phone: props.member?.phone || '',
  fax: props.member?.phone || ''
})

const submit = () => {
  router.put(route('profile.member.update'), form)
}
</script>

