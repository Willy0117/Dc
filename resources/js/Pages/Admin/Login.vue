<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-6">{{ t('login') }}</h2>
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label>{{ t('email') }}</label>
          <input type="email" v-model="form.email" class="w-full border rounded px-3 py-2"/>
        </div>
        <div class="mb-4">
          <label>{{ t('password') }}</label>
          <input type="password" v-model="form.password" class="w-full border rounded px-3 py-2"/>
        </div>
        <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded">{{ t('login') }}</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const form = ref({ email: '', password: '' });

function submit() {
  router.post('/admin/login', form.value);
}
</script>
