<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const show = ref(false)
const message = ref('')
const type = ref('success') // success or error
let timer = null

watch(
  () => page.props.flash.success ?? page.props.flash.error,
  (val) => {
    if (!val) return

    message.value = val
    type.value = page.props.flash.error ? 'error' : 'success'
    show.value = true

    if (timer) clearTimeout(timer)

    timer = setTimeout(() => {
      show.value = false
    }, 3000)
  }
)
/*
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success || flash?.error) {

      if (flash.success) {
        message.value = flash.success
        type.value = 'success'
      }

      if (flash.error) {
        message.value = flash.error
        type.value = 'error'
      }

      show.value = true

      if (timer) clearTimeout(timer)

      timer = setTimeout(() => {
        show.value = false
      }, 3000)
    }
  },
  { immediate: true }
)
*/
</script>

<template>
  <div class="fixed top-4 right-4 z-[9999]">
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 translate-y-2 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show"
           :class="[
             'px-4 py-3 rounded-xl shadow-lg flex items-center min-w-[250px] text-white',
             type === 'success' ? 'bg-green-500' : 'bg-red-500'
           ]">

        <span class="flex-1 text-sm">
          {{ message }}
        </span>

        <button
          @click="show = false"
          class="ml-3 w-6 h-6 flex items-center justify-center rounded hover:bg-white/20"
        >
          ×
        </button>

      </div>
    </transition>
  </div>
</template>