<script setup>
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  isAgent: {
    type: Boolean,
    default: false,
  },
})

const form = useForm({
  email: '',
  is_agent: props.isAgent, // ★これが命
})

</script>

<template>
  <GuestLayout>
    <div class="max-w-md mx-auto mt-8">
    
      <h1 class="text-xl font-bold mb-4">
      {{ t('email.title') }}
      </h1>
      <p class="text-sm text-gray-700 leading-relaxed">
          一般社団法人 全国中小建設工事業団体連合会への加盟申請ありがとうございます。
      </p>
      <div class="max-w-xl mx-auto bg-white p-4 space-y-4">
        <form @submit.prevent="form.post(route('pre-register.pre'))">
          
          <!-- email -->
          <div class="mb-4">
              <label class="block mb-1 text-sm font-medium">
              {{ t('email') }}
              </label>
              <input
              v-model="form.email"
              type="email"
              class="w-full border rounded px-3 py-2"
              />
          </div>
          <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
            {{ form.errors.email }}
          </div>
          
          <button
              type="submit"
              class="w-full bg-blue-600 text-white py-2 rounded disabled:opacity-50"
          >
              {{ t('email.submit') }}
          </button>
        </form>
      </div>
      <div class="max-w-xl mx-auto bg-white p-4 space-y-4">
        <p class="text-sm text-gray-700 leading-relaxed">
          加盟申請にあたり<strong class="font-medium">メールアドレスの確認</strong>を行っております。
        </p>

        <p class="text-sm text-gray-700 leading-relaxed">
          上記フォームにメールアドレスを入力し送信してください。<br>
          入力いただいたメールアドレス宛に、本登録用のURLをお送りいたします。
        </p>
      </div> 
    </div>
  </GuestLayout>
</template>
