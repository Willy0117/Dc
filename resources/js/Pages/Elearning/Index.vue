<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">e-ラーニング</p>
      <h1 class="text-xl font-semibold">動注治療 確認テスト</h1>
    </template>

    <div class="p-6 max-w-2xl space-y-4">

      <!-- 受験対象外の場合 -->
      <div v-if="!isEligible" class="border rounded-lg p-8 bg-white text-center space-y-2">
        <Info class="w-8 h-8 mx-auto text-muted-foreground opacity-40" />
        <p class="text-sm font-medium text-muted-foreground">確認テストの対象は先生となっています。契約先ではありません。</p>
        <p class="text-xs text-muted-foreground">対象アカウントについては運営事務局までお問い合わせください。</p>
      </div>

      <template v-else>
        <div class="border rounded-lg p-6 bg-white space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0" :class="isPassed ? 'bg-emerald-50' : 'bg-amber-50'">
              <component :is="isPassed ? CheckCircle2 : ClipboardList" class="w-6 h-6" :class="isPassed ? 'text-emerald-600' : 'text-amber-600'" />
            </div>
            <div>
              <p class="text-sm font-semibold">
                {{ isPassed ? '今期の確認テストに合格しています' : '今期の確認テストは未合格です' }}
              </p>
              <p class="text-xs text-muted-foreground">対象期間：{{ periodLabel }}</p>
            </div>
          </div>

          <p class="text-sm text-muted-foreground">
            全30問の中からランダムに15問出題されます。<strong class="text-foreground">12問以上正解</strong>で合格です。
            不合格の場合は、その場で何度でも再受験できます（設問は毎回変わります）。
          </p>

          <Button type="button" class="w-full" :disabled="starting" @click="startTest">
            <PlayCircle class="w-4 h-4 mr-1.5" />
            {{ isPassed ? 'もう一度受験する' : 'テストを開始する' }}
          </Button>
        </div>

        <!-- 今期の受験履歴 -->
        <div v-if="recentAttempts.length > 0" class="space-y-2">
          <p class="text-sm font-semibold text-muted-foreground">今期の受験履歴</p>
          <div class="border rounded-lg overflow-hidden">
            <div
              v-for="a in recentAttempts"
              :key="a.id"
              class="flex items-center justify-between px-4 py-3 odd:bg-white even:bg-muted/30 border-b last:border-b-0"
            >
              <span class="text-sm text-muted-foreground">{{ formatDate(a.submitted_at) }}</span>
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium">{{ a.correct_count }} / {{ a.total_questions }}問正解</span>
                <Badge v-if="a.is_passed" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-100">合格</Badge>
                <Badge v-else variant="destructive" class="opacity-80">不合格</Badge>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { PlayCircle, CheckCircle2, ClipboardList, Info } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

defineProps({
  isEligible:     { type: Boolean, default: true },
  isPassed:       { type: Boolean, default: false },
  recentAttempts: { type: Array, default: () => [] },
  periodLabel:    { type: String, default: '' },
})

const starting = ref(false)

function startTest() {
  starting.value = true
  router.post(route('elearning.start'), {}, {
    onFinish: () => { starting.value = false },
  })
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return dateStr.replace('T', ' ').slice(0, 16)
}
</script>