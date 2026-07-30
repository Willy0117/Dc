<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">e-ラーニング</p>
      <h1 class="text-xl font-semibold">テスト結果</h1>
    </template>

    <div class="p-6 max-w-2xl space-y-6">

      <!-- 結果サマリー -->
      <div class="border rounded-lg p-6 text-center space-y-3" :class="isPassed ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'">
        <component :is="isPassed ? CheckCircle2 : XCircle" class="w-10 h-10 mx-auto" :class="isPassed ? 'text-emerald-600' : 'text-amber-600'" />
        <p class="text-lg font-bold" :class="isPassed ? 'text-emerald-800' : 'text-amber-800'">
          {{ isPassed ? '合格' : '不合格' }}
        </p>
        <p class="text-sm text-muted-foreground">{{ correctCount }} / {{ totalQuestions }}問正解</p>
      </div>

      <div v-if="!isPassed" class="flex justify-center">
        <Button type="button" @click="retry">
          <RotateCcw class="w-4 h-4 mr-1.5" />もう一度受験する
        </Button>
      </div>
      <div v-else class="flex justify-center">
        <Button type="button" variant="outline" as-child>
          <Link :href="route('elearning.index')">テスト一覧に戻る</Link>
        </Button>
      </div>

      <!-- 各設問の正誤・解説 -->
      <div class="space-y-3">
        <p class="text-sm font-semibold text-muted-foreground">解答・解説</p>
        <div
          v-for="(a, index) in answers"
          :key="index"
          class="border rounded-lg p-4 space-y-2"
          :class="a.is_correct ? 'border-emerald-200' : 'border-red-200'"
        >
          <div class="flex items-start gap-2">
            <component :is="a.is_correct ? CheckCircle2 : XCircle" class="w-4 h-4 mt-0.5 shrink-0" :class="a.is_correct ? 'text-emerald-600' : 'text-red-600'" />
            <p class="text-sm font-medium">問{{ index + 1 }}．{{ a.question }}</p>
          </div>
          <div class="pl-6 space-y-1 text-sm">
            <p :class="a.selected_answer === a.correct_answer ? 'text-emerald-700' : 'text-red-700'">
              あなたの回答：{{ a.selected_answer }}. {{ a.choices[a.selected_answer] }}
            </p>
            <p v-if="!a.is_correct" class="text-muted-foreground">
              正解：{{ a.correct_answer }}. {{ a.choices[a.correct_answer] }}
            </p>
          </div>
          <p v-if="a.explanation" class="pl-6 text-xs text-muted-foreground bg-muted/50 rounded p-2">
            {{ a.explanation }}
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { CheckCircle2, XCircle, RotateCcw } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'

defineProps({
  correctCount:   { type: Number, default: 0 },
  totalQuestions: { type: Number, default: 0 },
  isPassed:       { type: Boolean, default: false },
  answers:        { type: Array, default: () => [] },
})

function retry() {
  router.post(route('elearning.start'))
}
</script>
