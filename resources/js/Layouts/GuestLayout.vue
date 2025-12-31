<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- head -->
    <slot name="head" />

    <!-- main -->
    <main class="flex-1 flex justify-center items-start pt-16">
      <div class="w-full max-w-5xl mt-6 px-8 py-8 bg-white shadow-md sm:rounded-lg">
        <slot />
      </div>
    </main>

    <!-- footer -->
    <footer class="border-t text-center text-xs text-gray-400 py-4">
      &copy {{ t('copyright') }}
    </footer>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>
