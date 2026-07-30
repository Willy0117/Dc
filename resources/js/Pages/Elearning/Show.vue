<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">e-ラーニング</p>
      <h1 class="text-xl font-semibold">確認テスト（{{ questions.length }}問）</h1>
    </template>

    <div class="p-6 max-w-2xl space-y-4">

      <!-- 進捗 -->
      <div class="sticky top-0 bg-background/95 backdrop-blur z-10 py-2 -mx-6 px-6 border-b">
        <div class="flex items-center justify-between text-sm mb-1.5">
          <span class="text-muted-foreground">回答済み {{ answeredCount }} / {{ questions.length }}問</span>
        </div>
        <div class="h-1.5 rounded-full bg-muted overflow-hidden">
          <div class="h-full bg-primary transition-all" :style="{ width: `${(answeredCount / questions.length) * 100}%` }" />
        </div>
      </div>

      <!-- 設問一覧 -->
      <div v-for="(q, index) in questions" :key="q.question_id" class="border rounded-lg p-5 bg-white space-y-3">
        <p class="text-sm font-semibold">
          <span class="text-muted-foreground">問{{ index + 1 }}．</span>{{ q.question }}
        </p>
        <div class="space-y-2">
          <label
            v-for="letter in ['A', 'B', 'C', 'D']"
            :key="letter"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg border cursor-pointer transition-colors"
            :class="answers[q.question_id] === letter
              ? 'border-primary bg-primary/5'
              : 'border-border hover:bg-muted/50'"
          >
            <input
              type="radio"
              :name="`q_${q.question_id}`"
              :value="letter"
              v-model="answers[q.question_id]"
              class="accent-primary"
            />
            <span class="text-sm">
              <span class="font-semibold text-muted-foreground mr-1">{{ letter }}.</span>{{ q.choices[letter] }}
            </span>
          </label>
        </div>
      </div>

      <!-- 送信 -->
      <div class="sticky bottom-0 bg-background/95 backdrop-blur border-t py-4 -mx-6 px-6">
        <p v-if="!allAnswered" class="text-xs text-destructive mb-2 text-center">
          未回答の設問が{{ questions.length - answeredCount }}問あります
        </p>
        <Button type="button" class="w-full" :disabled="!allAnswered || submitting" @click="submit">
          <Send class="w-4 h-4 mr-1.5" />
          {{ submitting ? '採点中...' : '回答を提出する' }}
        </Button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Send } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'

const props = defineProps({
  attemptId: { type: Number, required: true },
  questions: { type: Array, default: () => [] },
})

const answers = reactive({})
const submitting = ref(false)

const answeredCount = computed(() => Object.keys(answers).filter(k => answers[k]).length)
const allAnswered = computed(() => answeredCount.value === props.questions.length)

function submit() {
  if (!allAnswered.value) return
  submitting.value = true

  const payload = {
    answers: props.questions.map(q => ({
      question_id: q.question_id,
      selected: answers[q.question_id],
    })),
  }

  router.post(route('elearning.submit', props.attemptId), payload, {
    onFinish: () => { submitting.value = false },
  })
}
</script>
