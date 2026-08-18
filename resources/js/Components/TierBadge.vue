<template>
  <span v-if="tier > 1" class="inline-flex items-center gap-0.5" :title="config[tier].label">
    <Star
      v-for="n in tier - 1"
      :key="n"
      class="w-3 h-3"
      :class="config[tier].class"
    />
  </span>
</template>

<script setup>
import { Star } from 'lucide-vue-next'

defineProps({
  tier: { type: Number, default: 1 },
})

const config = {
  2: { label: 'シルバー', class: 'text-slate-400 fill-slate-400' },
  3: { label: 'ゴールド', class: 'text-yellow-500 fill-yellow-500' },
  4: { label: 'プラチナ', class: 'text-cyan-300 fill-cyan-300' },
}
</script>