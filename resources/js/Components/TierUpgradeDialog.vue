<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-sm">
      <DialogHeader>
        <DialogTitle>Tier変更</DialogTitle>
        <DialogDescription>
          {{ organization?.name }}
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-3 py-2">
        <div class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">現在のTier</span>
          <TierBadge :tier="organization?.tier" />
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">通算症例報告数</span>
          <span class="font-semibold">{{ totalCaseCount }}件</span>
        </div>

        <div class="border-t pt-3 space-y-2">
          <p class="text-xs text-muted-foreground">変更先のTierを選択</p>
          <div class="grid grid-cols-2 gap-2">
            <Button
              v-for="t in [1, 2, 3, 4]"
              :key="t"
              variant="outline"
              :class="organization?.tier === t ? 'border-primary bg-primary/10 font-semibold' : ''"
              :disabled="loading || organization?.tier === t"
              @click="changeTier(t)"
            >
              {{ tierLabels[t] }}（Tier{{ t }}）
            </Button>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import TierBadge from '@/Components/TierBadge.vue'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  organization: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:open', 'done'])

const loading = ref(false)

const tierLabels = { 1: 'ブロンズ', 2: 'シルバー', 3: 'ゴールド', 4: 'プラチナ' }

// 通算症例報告数(バックエンドから渡される想定。無ければ今期のみの値にフォールバック)
const totalCaseCount = computed(() =>
  props.organization?.total_case_count ?? props.organization?.current_tier_history?.case_count ?? 0
)

const changeTier = (tier) => {
  if (!confirm(`${props.organization.name} を ${tierLabels[tier]}（Tier${tier}）に変更しますか？`)) return

  loading.value = true
  router.post(
    route('admin.organizations.upgrade-tier', props.organization.id),
    { tier },
    {
      preserveState: true,
      onSuccess: () => {
        emit('update:open', false)
        emit('done')
      },
      onFinish: () => { loading.value = false },
    }
  )
}
</script>