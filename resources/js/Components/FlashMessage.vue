<template>
  <transition name="fade">
    <div
      v-if="message"
      :class="typeClass"
      class="p-3 rounded-lg shadow-md mb-4"
    >
      {{ message }}
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const message = computed(
  () =>
    page.props.flash.success ||
    page.props.flash.error ||
    page.props.flash.warning ||
    null
)

const type = computed(() => {
  if (page.props.flash.success) return 'success'
  if (page.props.flash.error) return 'error'
  if (page.props.flash.warning) return 'warning'
  return null
})

const typeClass = computed(() => {
  switch (type.value) {
    case 'success':
      return 'bg-green-100 text-green-800 border border-green-300'
    case 'error':
      return 'bg-red-100 text-red-800 border border-red-300'
    case 'warning':
      return 'bg-yellow-100 text-yellow-800 border border-yellow-300'
    default:
      return ''
  }
})
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
