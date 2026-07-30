<template>
  <Triangle
    v-if="isActive"
    class="w-2.5 h-2.5 ml-1 inline-block text-foreground fill-foreground transition-transform"
    :class="dir === 'asc' ? '' : 'rotate-180'"
  />
</template>

<script setup>
import { computed } from 'vue'
import { Triangle } from 'lucide-vue-next'

const props = defineProps({
  field:   { type: String, required: true },
  current: { type: String, default: '' },
  dir:     { type: String, default: 'desc' },
})

const isActive = computed(() => props.current === props.field)
</script>