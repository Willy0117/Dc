<template>
  <span class="inline-flex flex-col ml-1 -space-y-1">
    <ChevronUp
      class="w-3 h-3 transition-colors"
      :class="current === field && dir === 'asc'
        ? 'text-foreground opacity-100'
        : 'text-muted-foreground/30'"
    />
    <ChevronDown
      class="w-3 h-3 transition-colors"
      :class="current === field && dir === 'desc'
        ? 'text-foreground opacity-100'
        : 'text-muted-foreground/30'"
    />
  </span>
</template>
 
<script setup>
import { ChevronUp, ChevronDown } from 'lucide-vue-next'
 
defineProps({
  field:   { type: String, required: true },
  current: { type: String, default: '' },
  dir:     { type: String, default: 'desc' },
})
</script>