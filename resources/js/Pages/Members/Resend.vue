<template>
  <GuestLayout>
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow text-center">
      <h2 class="text-xl font-bold mb-4">有効期限切れ</h2>
        <p
            class="text-gray-600 whitespace-pre-line"
            >
            {{ message }}
        </p>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const message = props.message;
</script>