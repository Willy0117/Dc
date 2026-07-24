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
  2: { label: 'アドバンス',   class: 'text-amber-700 fill-amber-700' },  // 銅
  3: { label: 'エキスパート', class: 'text-slate-400 fill-slate-400' },  // 銀
  4: { label: 'マスター',     class: 'text-yellow-400 fill-yellow-400' }, // 金
}
</script>