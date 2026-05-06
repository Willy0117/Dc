<template>
  <GuestLayout>
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow text-center">
      <h2 class="text-xl font-bold mb-4">ご登録ありがとうございました</h2>
      <p class="text-blue-600">この度は、一般社団法人 全国中小建設工事業団体連合会への<br>
      入会申請ありがとうございました。<br><br>
      入会の承認については、一定の審査をさせていただきますので、<br>
      どうぞ今しばらくお待ちください。<br>
      </p>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import RegisterStep from '@/Components/RegisterStep.vue'    

import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const message = props.message;
</script>
