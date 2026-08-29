<template>
  <div
    v-if="member"
    class="flex items-center gap-3 px-3 py-1.5 rounded-full border"
    :class="toneClass"
  >
    <!-- 現在のTier（星＋名称、Tier1は星なし・名称のみ） -->
    <span class="flex items-center gap-1 font-semibold">
      現在のグレード：
      <TierBadge v-if="member.tier > 1" :tier="member.tier" />
      {{ currentTierLabel }}
    </span>

    <span class="font-medium">
      現在：<span class="font-bold">{{ totalCaseCount }}</span>件
    </span>

    <span v-if="nextThreshold" class="text-xs border-l border-current/20 pl-3">
      次の{{ nextTierLabel }}まであと <span class="font-bold">{{ remaining }}</span>件
    </span>
    <span v-else class="text-xs border-l border-current/20 pl-3 font-semibold">
      最高グレードです
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TierBadge from '@/Components/TierBadge.vue'

const props = defineProps({
  member: {
    type: Object,
    default: null,
  },
})

// Tierしきい値・ラベル（Member.php::TIER_LABELS / calculateTierFromCaseCount() と同じ値）
const TIER_THRESHOLDS = { 2: 100, 3: 300, 4: 1000 }
const TIER_LABELS = { 1: 'ベーシック', 2: 'アドバンス', 3: 'エキスパート', 4: 'マスター' }

// Tierごとに背景・文字色を変えて、目立たせる
const TIER_TONES = {
  1: 'bg-primary/5 border-primary/20 text-primary',
  2: 'bg-slate-100 border-slate-300 text-slate-700',
  3: 'bg-yellow-50 border-yellow-300 text-yellow-800',
  4: 'bg-cyan-50 border-cyan-300 text-cyan-800',
}

const currentTier = computed(() => props.member?.tier ?? 1)
const toneClass = computed(() => TIER_TONES[currentTier.value] ?? TIER_TONES[1])
const currentTierLabel = computed(() => TIER_LABELS[currentTier.value] ?? TIER_LABELS[1])

const totalCaseCount = computed(() =>
  props.member?.total_case_count ?? props.member?.current_tier_history?.case_count ?? 0
)

// 現在のtierの次にあたるTier番号・しきい値・名称
const nextTier = computed(() => currentTier.value + 1)
const nextThreshold = computed(() => TIER_THRESHOLDS[nextTier.value] ?? null)
const nextTierLabel = computed(() => TIER_LABELS[nextTier.value] ?? '')

const remaining = computed(() => {
  if (!nextThreshold.value) return 0
  return Math.max(0, nextThreshold.value - totalCaseCount.value)
})
</script>