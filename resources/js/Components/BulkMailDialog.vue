<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/40" @click="$emit('update:open', false)" />
      <div class="relative bg-background rounded-lg shadow-xl w-full max-w-lg mx-4 z-10">

        <!-- ヘッダー -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h2 class="font-bold text-base">一括メール送信</h2>
          <Button variant="ghost" size="icon" @click="$emit('update:open', false)">
            <X class="w-4 h-4" />
          </Button>
        </div>

        <!-- 本文 -->
        <div class="px-6 py-5 space-y-4">
          <!-- 送信先確認 -->
          <div class="text-sm text-muted-foreground bg-muted/50 rounded-md px-3 py-2">
            送信先: <span class="font-medium text-foreground">{{ targets.length }}件</span> の組織
          </div>

          <!-- 件名 -->
          <div class="space-y-1.5">
            <Label for="bulk-mail-subject">件名 <span class="text-destructive">*</span></Label>
            <Input
              id="bulk-mail-subject"
              v-model="form.subject"
              placeholder="件名を入力"
              :class="{ 'border-destructive': errors.subject }"
            />
            <p v-if="errors.subject" class="text-xs text-destructive">{{ errors.subject }}</p>
          </div>

          <!-- 本文 -->
          <div class="space-y-1.5">
            <Label for="bulk-mail-body">本文 <span class="text-destructive">*</span></Label>
            <Textarea
              id="bulk-mail-body"
              v-model="form.body"
              placeholder="本文を入力&#10;&#10;※ {organization_name} と記入すると組織名に自動で置き換わります"
              rows="8"
              :class="{ 'border-destructive': errors.body }"
            />
            <p v-if="errors.body" class="text-xs text-destructive">{{ errors.body }}</p>
            <p class="text-xs text-muted-foreground">
              ヒント: <code class="bg-muted px-1 rounded">{organization_name}</code> で組織名に自動置換されます
            </p>
          </div>
        </div>

        <!-- フッター -->
        <div class="flex justify-end gap-2 px-6 py-4 border-t">
          <Button variant="outline" @click="$emit('update:open', false)">キャンセル</Button>
          <Button :disabled="sending" @click="submit">
            <Loader2 v-if="sending" class="w-3.5 h-3.5 mr-1 animate-spin" />
            <Send v-else class="w-3.5 h-3.5 mr-1" />
            {{ sending ? '送信中...' : `${targets.length}件に送信` }}
          </Button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { X, Send, Loader2 } from 'lucide-vue-next'
import { Button }   from '@/components/ui/button'
import { Input }    from '@/components/ui/input'
import { Label }    from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

const props = defineProps({
  open:    { type: Boolean, default: false },
  targets: { type: Array,   default: () => [] },
})

const emit = defineEmits(['update:open', 'done'])

const form = reactive({
  subject: '',
  body:    '',
})

const errors  = reactive({ subject: '', body: '' })
const sending = ref(false)

// ダイアログを開くたびにフォームをリセット
watch(() => props.open, (val) => {
  if (val) {
    form.subject   = ''
    form.body      = ''
    errors.subject = ''
    errors.body    = ''
  }
})

const validate = () => {
  errors.subject = form.subject.trim() ? '' : '件名を入力してください'
  errors.body    = form.body.trim()    ? '' : '本文を入力してください'
  return !errors.subject && !errors.body
}

const submit = () => {
  if (!validate()) return

  sending.value = true
  router.post(
    route('admin.organizations.bulk-send-mail'),
    {
      ids:     props.targets.map(t => t.id),
      subject: form.subject,
      body:    form.body,
    },
    {
      preserveState: true,
      onSuccess: () => {
        sending.value = false
        emit('update:open', false)
        emit('done')
      },
      onError: (err) => {
        sending.value = false
        Object.assign(errors, err)
      },
    }
  )
}
</script>
