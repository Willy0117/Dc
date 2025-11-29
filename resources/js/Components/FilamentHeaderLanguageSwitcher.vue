<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const open = ref(false)

if (window.Livewire) {
    window.Livewire.on('languageSwitched', (newLocale) => {
        locale.value = newLocale
    })
}

function switchLocale(code) {
    if (window.Livewire) {
        window.Livewire.emit('languageSwitched', code)
    }
    locale.value = code
    open.value = false
}
</script>

<template>
  <div class="relative inline-block text-left">
    <button @click="open = !open" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
      {{ locale.toUpperCase() }}
    </button>
    <div v-if="open" class="absolute right-0 mt-2 w-24 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
      <div class="py-1">
        <button @click="switchLocale('en')" class="block w-full px-4 py-2 text-left hover:bg-gray-100">EN</button>
        <button @click="switchLocale('ja')" class="block w-full px-4 py-2 text-left hover:bg-gray-100">JA</button>
      </div>
    </div>
  </div>
</template>
