<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-3xl">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <CreditCard class="w-5 h-5 text-primary" />
          Stripe 支払い
        </DialogTitle>
        <DialogDescription>
          対象組織の年次請求に対して Stripe 支払いリンクを発行します。
        </DialogDescription>
      </DialogHeader>

      <!-- 対象一覧 -->
      <div class="rounded-md border overflow-hidden">
        <div class="bg-muted/50 px-3 py-2 text-xs font-semibold text-muted-foreground uppercase tracking-wide border-b">
          対象 {{ targets.length }} 件
        </div>
        <ul class="divide-y max-h-48 overflow-y-auto">
          <li
            v-for="org in targets"
            :key="org.id"
            class="px-3 py-2 flex items-center justify-between text-sm"
          >
            <span class="font-medium">{{ org.name }}</span>
            <span class="text-muted-foreground text-xs flex items-center gap-1.5">
              <Calendar class="w-3 h-3" />
              次回請求:
              <span class="text-primary font-semibold">
                {{ nextBillingDate(org.contract_date) }}
              </span>
            </span>
          </li>
        </ul>
      </div>

      <!-- 料金自動計算プレビュー（単発のみ） -->
      <div v-if="feeLoading" class="text-xs text-muted-foreground flex items-center gap-1.5">
        <Loader2 class="w-3.5 h-3.5 animate-spin" />料金を計算中...
      </div>
      <div v-else-if="feeInfo" class="bg-violet-50 border border-violet-100 rounded-lg px-4 py-3 text-sm space-y-1">
        <div class="flex justify-between text-muted-foreground">
          <span>送信先メール</span>
          <span :class="feeInfo.email ? 'font-medium text-gray-700' : 'text-red-500'">
            {{ feeInfo.email || '未登録（送信先メールが必要です）' }}
          </span>
        </div>
        <div class="flex justify-between text-muted-foreground">
          <span>登録人数</span><span>{{ feeInfo.member_count }}名</span>
        </div>
        <div class="flex justify-between text-muted-foreground">
          <span>小計（税抜）</span><span>¥{{ feeInfo.subtotal.toLocaleString() }}</span>
        </div>
        <div class="flex justify-between text-muted-foreground">
          <span>消費税（10%）</span><span>¥{{ feeInfo.tax.toLocaleString() }}</span>
        </div>
        <div class="flex justify-between font-bold text-violet-700 pt-1 border-t border-violet-200">
          <span>合計（税込）</span><span>¥{{ feeInfo.total.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 支払い設定フォーム -->
      <div class="space-y-3 pt-1">
        <div class="space-y-1.5">
          <Label for="stripe-amount">請求金額（円）</Label>
          <Input
            id="stripe-amount"
            v-model="stripeForm.amount"
            type="number"
            min="0"
            placeholder="例: 120000"
          />
        </div>

        <div class="space-y-1.5">
          <Label for="stripe-description">支払い説明文</Label>
          <Input
            id="stripe-description"
            v-model="stripeForm.description"
            placeholder="例: 年次サービス利用料（2025年度）"
          />
        </div>

        <div class="space-y-1.5">
          <Label>支払い方法</Label>
          <div class="flex gap-3">
            <label
              v-for="method in paymentMethods"
              :key="method.value"
              class="flex items-center gap-2 cursor-pointer text-sm"
            >
              <input
                type="checkbox"
                :value="method.value"
                v-model="stripeForm.payment_methods"
                class="rounded border-input"
              />
              {{ method.label }}
            </label>
          </div>
        </div>

      </div>
      <!-- ステータス表示 -->
      <div
        v-if="resultMessage"
        :class="[
          'rounded-md px-3 py-2 text-sm flex items-center gap-2',
          isError ? 'bg-destructive/10 text-destructive' : 'bg-green-50 text-green-700'
        ]"
      >
        <CheckCircle2 v-if="!isError" class="w-4 h-4 flex-shrink-0" />
        <AlertCircle  v-else        class="w-4 h-4 flex-shrink-0" />
        {{ resultMessage }}
      </div>

      <DialogFooter class="gap-2">
        <Button variant="outline" @click="$emit('update:open', false)" :disabled="loading">
          キャンセル
        </Button>
        <Button variant="default" @click="submit" :disabled="loading || !stripeForm.amount">
          <Loader2 v-if="loading" class="w-4 h-4 mr-1.5 animate-spin" />
          <CreditCard v-else class="w-4 h-4 mr-1.5" />
          支払いリンクを発行してメール送信
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import axios from 'axios'
import {
  CreditCard, Calendar, Copy,
  CheckCircle2, AlertCircle, Loader2,
} from 'lucide-vue-next'

import {
  Dialog, DialogContent, DialogHeader,
  DialogTitle, DialogDescription, DialogFooter,
} from '@/components/ui/dialog'
import { Button }   from '@/components/ui/button'
import { Input }    from '@/components/ui/input'
import { Label }    from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'

// ────────────────────────────────────────
// Props / Emits
// ────────────────────────────────────────
const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  targets: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:open', 'done'])

// ────────────────────────────────────────
// フォーム状態
// ────────────────────────────────────────
const loading       = ref(false)
const resultMessage = ref('')
const isError       = ref(false)
const generatedLink = ref('')
const copied        = ref(false)

const paymentMethods = [
  { value: 'card',          label: 'クレジットカード' },
]

const stripeForm = reactive({
  amount:          '',
  description:     '',
  payment_methods: ['card'],
  send_email:      true,
})

// ────────────────────────────────────────
// 料金自動計算（単発のみ対応。targets[0] を参照）
// ────────────────────────────────────────
const feeLoading = ref(false)
const feeInfo    = ref(null)

const fetchFee = async () => {
  if (props.targets.length !== 1) {
    feeInfo.value = null
    return
  }

  feeLoading.value = true
  try {
    const { data } = await axios.get(route('admin.organizations.fee', props.targets[0].id))
    feeInfo.value = data
    stripeForm.amount = data.total
  } catch (e) {
    console.error('料金取得に失敗しました', e)
    feeInfo.value = null
  } finally {
    feeLoading.value = false
  }
}

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    resultMessage.value = ''
    isError.value       = false
    generatedLink.value = ''
    fetchFee()
  }
})

// ────────────────────────────────────────
// 年次請求日の計算
// ────────────────────────────────────────
const nextBillingDate = (contractDate) => {
  if (!contractDate) return '-'
  return dayjs(contractDate).add(1, 'year').format('YYYY/MM/DD')
}

// ────────────────────────────────────────
// リンクコピー
// ────────────────────────────────────────
const copyLink = () => {
  if (!generatedLink.value) return
  navigator.clipboard.writeText(generatedLink.value)
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

// ────────────────────────────────────────
// 送信
// ────────────────────────────────────────
const submit = () => {
  if (!stripeForm.amount) return

  loading.value       = true
  resultMessage.value = ''
  isError.value       = false
  generatedLink.value = ''

  const ids = props.targets.map(o => o.id)

  router.post(
    route('admin.organizations.stripe_payment'),
    {
      organization_ids: ids,
      amount:           stripeForm.amount,
      description:      stripeForm.description,
      payment_methods:  stripeForm.payment_methods,
      send_email:       stripeForm.send_email,
      base:             feeInfo.value?.base  ?? null,
      extra:            feeInfo.value?.extra ?? null,

    },
    {
      preserveState: true,
      onSuccess: (page) => {
        // 単発の場合はリンクを表示
        const link = page.props.flash?.stripe_link
        if (link) generatedLink.value = link

        resultMessage.value = stripeForm.send_email
          ? `支払いリンクを発行し、${ids.length} 件にメールを送信しました。`
          : `支払いリンクを ${ids.length} 件発行しました。`
        isError.value = false
        emit('done')
        if (!link) setTimeout(() => emit('update:open', false), 1500)
      },
      onError: (errors) => {
        resultMessage.value = Object.values(errors)[0] ?? '処理に失敗しました。'
        isError.value = true
      },
      onFinish: () => {
        loading.value = false
      },
    }
  )
}
</script>