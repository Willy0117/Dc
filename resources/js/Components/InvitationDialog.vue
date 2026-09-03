<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Mail class="w-5 h-5 text-primary" />
          申込メール送信
        </DialogTitle>
        <DialogDescription>
          送信内容を確認・変更してから送信してください。
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4">

        <!-- 送信先組織名 -->
        <div class="bg-muted/50 rounded-lg px-4 py-2 text-sm font-medium">
          {{ organization?.name }}
        </div>

        <!-- 読み込み中 -->
        <div v-if="feeLoading" class="text-xs text-muted-foreground flex items-center gap-1.5">
          <Loader2 class="w-3.5 h-3.5 animate-spin" />情報を取得中...
        </div>

        <!-- 送信先メールアドレス -->
        <div class="space-y-1.5">
          <Label for="invitation-email">送信先メールアドレス <span class="text-destructive">*</span></Label>
          <Input
            id="invitation-email"
            v-model="form.email"
            type="email"
            placeholder="example@example.com"
          />
        </div>

        <!-- 契約日 -->
        <div class="space-y-1.5">
          <Label for="invitation-contract-date">契約日 <span class="text-destructive">*</span></Label>
          <Input
            id="invitation-contract-date"
            v-model="form.contract_date"
            type="date"
          />
        </div>

        <!-- 新契約日 -->
        <div class="space-y-1.5">
          <Label for="invitation-new-contract-date">新契約日 <span class="text-destructive">*</span></Label>
          <Input
            id="invitation-new-contract-date"
            v-model="form.new_contract_date"
            type="date"
          />
          <p class="text-xs text-muted-foreground">次回更新日（通常は契約日の1年後）</p>
        </div>

        <!-- 契約種別 -->
        <div class="space-y-1.5">
          <Label>契約種別</Label>
          <div class="flex gap-2">
            <button
              type="button"
              class="flex-1 px-3 py-2 rounded-lg border text-sm transition-all"
              :class="!form.needs_agreement
                ? 'border-blue-500 bg-blue-50 text-blue-800 font-semibold'
                : 'border-border bg-background text-muted-foreground hover:bg-muted'"
              @click="selectNewContract"
            >
              新規契約（契約書のみ）
            </button>
            <button
              type="button"
              class="flex-1 px-3 py-2 rounded-lg border text-sm transition-all"
              :class="form.needs_agreement
                ? 'border-blue-500 bg-blue-50 text-blue-800 font-semibold'
                : 'border-border bg-background text-muted-foreground hover:bg-muted'"
              @click="selectRenewal"
            >
              再契約（契約書＋合意書）
            </button>
          </div>
        </div>

      </div>

      <!-- エラー表示 -->
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
        <Button variant="default" @click="submit" :disabled="loading || !form.email || !form.contract_date">
          <Loader2 v-if="loading" class="w-4 h-4 mr-1.5 animate-spin" />
          <Mail v-else class="w-4 h-4 mr-1.5" />
          申込メールを送信
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import dayjs from 'dayjs'
import { Mail, AlertCircle, Loader2 } from 'lucide-vue-next'

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
  organization: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:open', 'done'])

const loading      = ref(false)
const feeLoading   = ref(false)
const errorMessage = ref('')

const form = reactive({
  email:             '',
  contract_date:     dayjs().format('YYYY-MM-DD'),
  new_contract_date: dayjs().format('YYYY-MM-DD'),
  needs_agreement:   false,
})

// 変更点：新規/再契約の切り替え（ボタンクリック時・ダイアログを開いた時の
// 自動判定時、どちらからも呼ぶ共通関数）。
// 契約書PDFにはnew_contract_dateが印字されるため、新規契約の初期値を
// 「契約日＋1年」にしてしまうと契約書の日付が来年にズレるバグがあった。
function selectNewContract() {
  form.needs_agreement = false
  // 新規契約：contract_dateがまだ無いので、両方とも今日にする
  form.contract_date = dayjs().format('YYYY-MM-DD')
  form.new_contract_date = dayjs().format('YYYY-MM-DD')
}

function selectRenewal() {
  form.needs_agreement = true
  // 再契約：既存の契約日（無ければ今日）を表示し、
  // 新契約日は既存値、無ければ契約日の1年後をデフォルトにする
  form.contract_date = props.organization?.contract_date
    ? dayjs(props.organization.contract_date).format('YYYY-MM-DD')
    : dayjs().format('YYYY-MM-DD')
  form.new_contract_date = props.organization?.new_contract_date
    ? dayjs(props.organization.new_contract_date).format('YYYY-MM-DD')
    : dayjs(form.contract_date).add(1, 'year').format('YYYY-MM-DD')
}

// fee APIからメールアドレスを取得
const fetchFee = async () => {
  if (!props.organization) return

  feeLoading.value = true
  try {
    const { data } = await axios.get(route('admin.organizations.fee', props.organization.id))
    form.email = data.email ?? ''
  } catch (e) {
    console.error('メールアドレスの取得に失敗しました', e)
  } finally {
    feeLoading.value = false
  }
}

watch(() => props.open, (isOpen) => {
  if (isOpen && props.organization) {
    errorMessage.value = ''

    // 変更点：organization.contract_date が入っているかどうかで
    // 「再契約」か「新規契約」かを自動判定し、
    // ボタンクリック時と同じ関数で日付もまとめてセットする。
    if (props.organization.contract_date) {
      selectRenewal()
    } else {
      selectNewContract()
    }

    fetchFee()
  }
})

const submit = () => {
  if (!form.email || !form.contract_date) return

  loading.value      = true
  errorMessage.value = ''

  router.post(
    route('admin.organizations.send-invitation', props.organization.id),
    {
      email:             form.email,
      contract_date:     form.contract_date,
      new_contract_date: form.new_contract_date,
      needs_agreement:   form.needs_agreement,
    },
    {
      preserveState: true,
      onSuccess: () => {
        emit('done')
        emit('update:open', false)
      },
      onError: (errors) => {
        errorMessage.value = Object.values(errors)[0] ?? '送信に失敗しました。'
      },
      onFinish: () => {
        loading.value = false
      },
    }
  )
}
</script>