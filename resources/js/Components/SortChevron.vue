<template>
  <ChevronDown
    class="w-3.5 h-3.5 transition-all duration-150"
    :class="isActive
      ? 'opacity-100 text-foreground ' + (dir === 'asc' ? 'rotate-180' : '')
      : 'opacity-0 group-hover:opacity-40 text-muted-foreground'"
  />
</template>

<script setup>
import { computed } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

const props = defineProps({
  field: { type: String, required: true },
  current: { type: String, default: null },
  dir: { type: String, default: 'asc' }, // 'asc' | 'desc'
})

const isActive = computed(() => props.current === props.field)
</script>