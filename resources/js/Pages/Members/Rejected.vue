<template>
  <GuestLayout>
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow text-center">
      <h2 class="text-xl font-bold mb-4">お知らせ</h2>
      <p class="text-red-600">{{ message }}</p>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const message = props.message;
</script>
