<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-sm">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <CheckCircle2 class="w-5 h-5 text-green-600" />
          支払済みに変更
        </DialogTitle>
        <DialogDescription>
          実際の入金日を入力してください。
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-1.5">
        <Label for="paid-at">入金日</Label>
        <Input id="paid-at" v-model="paidAt" type="date" />
      </div>

      <div
        v-if="errorMessage"
        class="rounded-md bg-destructive/10 text-destructive px-3 py-2 text-sm flex items-center gap-2"
      >
        <AlertCircle class="w-4 h-4 flex-shrink-0" />
        {{ errorMessage }}
      </div>

      <DialogFooter class="gap-2">
        <Button variant="outline" @click="$emit('update:open', false)" :disabled="loading">
          キャンセル
        </Button>
        <Button variant="default" @click="submit" :disabled="loading || !paidAt">
          <Loader2 v-if="loading" class="w-4 h-4 mr-1.5 animate-spin" />
          <CheckCircle2 v-else class="w-4 h-4 mr-1.5" />
          支払済みにする
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { CheckCircle2, AlertCircle, Loader2 } from 'lucide-vue-next'

import {
  Dialog, DialogContent, DialogHeader,
  DialogTitle, DialogDescription, DialogFooter,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input }  from '@/components/ui/input'
import { Label }  from '@/components/ui/label'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  invoice: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:open', 'done'])

const loading      = ref(false)
const errorMessage  = ref('')
const paidAt        = ref(dayjs().format('YYYY-MM-DD'))

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    errorMessage.value = ''
    paidAt.value = dayjs().format('YYYY-MM-DD')
  }
})

const submit = () => {
  if (!paidAt.value || !props.invoice) return

  loading.value = true
  errorMessage.value = ''

  router.patch(
    route('admin.invoices.update', props.invoice.id),
    {
      status:  2, // 支払済み
      paid_at: paidAt.value,
    },
    {
      preserveState: true,
      onSuccess: () => {
        emit('done')
        emit('update:open', false)
      },
      onError: (errors) => {
        errorMessage.value = Object.values(errors)[0] ?? '更新に失敗しました。'
      },
      onFinish: () => {
        loading.value = false
      },
    }
  )
}
</script>
