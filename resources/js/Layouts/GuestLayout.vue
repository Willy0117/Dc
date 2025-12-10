<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
    <!-- ページのタイトルを head に反映 -->
    <slot name="head"></slot>

    <div class="w-full max-w-5xl mt-6 px-8 py-8 bg-white shadow-md sm:rounded-lg">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
// 今は特にスクリプトは不要
</script>

<style scoped>
/* 必要に応じて追加 */
</style>
