<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-sm">
      <DialogHeader>
        <DialogTitle>Tier昇格</DialogTitle>
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
          <span class="text-muted-foreground">今期の症例報告数</span>
          <span class="font-semibold">{{ organization?.current_tier_history?.case_count ?? 0 }}件</span>
        </div>

        <div class="border-t pt-3 space-y-2">
          <p class="text-xs text-muted-foreground">昇格先を選択</p>

          <Button
            v-if="caseCount >= 90 && organization?.tier < 3"
            class="w-full"
            variant="outline"
            :disabled="loading"
            @click="upgrade(3)"
          >
            エキスパート（Tier3）に昇格
          </Button>

          <Button
            v-if="caseCount >= 120 && organization?.tier < 4"
            class="w-full"
            :disabled="loading"
            @click="upgrade(4)"
          >
            マスター（Tier4）に昇格
          </Button>

          <p
            v-if="caseCount < 90"
            class="text-xs text-muted-foreground text-center py-2"
          >
            Tier3昇格には年間90件以上が必要です（現在: {{ caseCount }}件）
          </p>
          <p
            v-else-if="caseCount >= 90 && caseCount < 120 && organization?.tier >= 3"
            class="text-xs text-muted-foreground text-center py-2"
          >
            Tier4昇格には年間120件以上が必要です（現在: {{ caseCount }}件）
          </p>
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

const caseCount = computed(() => props.organization?.current_tier_history?.case_count ?? 0)

const upgrade = (tier) => {
  if (!confirm(`${props.organization.name} を ${tier === 3 ? 'エキスパート' : 'マスター'}（Tier${tier}）に昇格しますか？`)) return

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