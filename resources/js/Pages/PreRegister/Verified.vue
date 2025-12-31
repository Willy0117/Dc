<script setup>
import { Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  email: String,
  token: String,
})
</script>

<template>
  <GuestLayout>

    <div class="max-w-md mx-auto mt-16 text-center">
        <div class="text-green-600 text-3xl mb-4">✔</div>

        <h1 class="text-xl font-bold mb-2">
        {{ t('pre_register.verified.title') }}
        </h1>

        <p class="text-gray-600 mb-6">
        {{ t('pre_register.verified.description', { email }) }}
        </p>

        <Link
        :href="route('pre_register.form', token)"
        class="inline-block px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
        {{ t('pr-register.verified.action') }}
        </Link>
    </div>
  </GuestLayout>
</template>
