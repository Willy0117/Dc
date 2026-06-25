<template>
  <AppLayout>
    <template #header>Stripe 支払い管理</template>

    <div class="p-6 space-y-4">

      <!-- ステータスサマリー -->
      <div class="flex items-center gap-2 flex-wrap">
        <div class="flex items-center gap-1.5 rounded-md border bg-white px-3 py-1.5 text-sm shadow-sm">
          <FileText class="w-3.5 h-3.5 text-gray-400" />
          <span class="text-muted-foreground text-xs">未送信</span>
          <span class="font-bold tabular-nums ml-1">{{ summary.unsent }}</span>
          <span class="text-muted-foreground text-xs">件</span>
        </div>
        <div class="flex items-center gap-1.5 rounded-md border bg-white px-3 py-1.5 text-sm shadow-sm">
          <Send class="w-3.5 h-3.5 text-blue-500" />
          <span class="text-muted-foreground text-xs">送信済み</span>
          <span class="font-bold tabular-nums ml-1">{{ summary.sent }}</span>
          <span class="text-muted-foreground text-xs">件</span>
        </div>
        <div class="flex items-center gap-1.5 rounded-md border bg-white px-3 py-1.5 text-sm shadow-sm">
          <CheckCircle2 class="w-3.5 h-3.5 text-green-500" />
          <span class="text-muted-foreground text-xs">支払済み</span>
          <span class="font-bold tabular-nums ml-1">{{ summary.paid }}</span>
          <span class="text-muted-foreground text-xs">件</span>
        </div>
        <div class="flex items-center gap-1.5 rounded-md border bg-white px-3 py-1.5 text-sm shadow-sm">
          <AlertTriangle class="w-3.5 h-3.5 text-red-500" />
          <span class="text-muted-foreground text-xs">期限超過</span>
          <span class="font-bold tabular-nums ml-1 text-destructive">{{ summary.overdue }}</span>
          <span class="text-muted-foreground text-xs">件</span>
        </div>
      </div>

      <!-- ツールバー -->
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <Select v-model="form.per_page" @update:modelValue="submitSearch">
            <SelectTrigger class="w-20 h-9"><SelectValue /></SelectTrigger>
            <SelectContent>
              <SelectItem v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</SelectItem>
            </SelectContent>
          </Select>

          <template v-if="selectedIds.length > 0">
            <Button variant="outline" size="sm" @click="bulkResendEmail">
              <Mail class="w-3.5 h-3.5 mr-1" />{{ selectedIds.length }}件メール再送
            </Button>
            <Button variant="outline" size="sm" @click="bulkMarkAsPaid">
              <CheckCircle2 class="w-3.5 h-3.5 mr-1" />{{ selectedIds.length }}件支払済みに
            </Button>
          </template>
        </div>

        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="openDrawer = true">
            <Search class="w-3.5 h-3.5 mr-1" />検索
          </Button>
        </div>
      </div>

      <!-- 検索中バッジ -->
      <div v-if="hasActiveFilters" class="flex items-center gap-2 flex-wrap">
        <span class="text-xs text-muted-foreground">検索条件:</span>
        <Badge v-if="form.keyword" variant="secondary" class="gap-1">
          キーワード: {{ form.keyword }}
          <button @click="form.keyword = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.status !== ''" variant="secondary" class="gap-1">
          ステータス: {{ statusLabels[form.status] }}
          <button @click="form.status = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 w-8">
                <Checkbox :checked="selectAll" @update:checked="toggleSelectAll" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('invoice_no')">
                請求書No. <SortIcon field="invoice_no" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('organization_id')">
                契約先 <SortIcon field="organization_id" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('amount')">
                金額 <SortIcon field="amount" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('due_date')">
                支払期限 <SortIcon field="due_date" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('status')">
                ステータス <SortIcon field="status" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                支払いリンク
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('paid_at')">
                支払日 <SortIcon field="paid_at" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                操作
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="invoices.data.length === 0">
              <td colspan="9" class="px-3 py-12 text-center text-muted-foreground">
                <CreditCard class="w-8 h-8 mx-auto mb-2 opacity-30" />
                Stripe支払いデータが見つかりません
              </td>
            </tr>
            <tr
              v-for="invoice in invoices.data"
              :key="invoice.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5">
                <Checkbox :value="invoice.id" v-model:checked="selectedIds" />
              </td>
              <td class="px-3 py-2.5 font-mono text-xs text-muted-foreground">
                {{ invoice.invoice_no }}
              </td>
              <td class="px-3 py-2.5">
                <Link :href="route('admin.organizations.show', invoice.organization_id)" class="font-medium hover:underline">
                  {{ invoice.organization?.name ?? '-' }}
                </Link>
                <div class="text-xs text-muted-foreground mt-0.5">{{ billingEmail(invoice) }}</div>
              </td>
              <td class="px-3 py-2.5 font-medium tabular-nums">
                ¥{{ Number(invoice.amount).toLocaleString() }}
              </td>
              <td class="px-3 py-2.5 text-sm" :class="isOverdue(invoice) ? 'text-destructive font-semibold' : 'text-muted-foreground'">
                {{ invoice.due_date ? dayjs(invoice.due_date).format('YYYY/MM/DD') : '-' }}
                <span v-if="isOverdue(invoice)" class="text-xs ml-1">超過</span>
              </td>
              <td class="px-3 py-2.5">
                <Badge :variant="statusVariant(invoice.status)" class="text-xs">
                  {{ statusLabels[invoice.status] ?? '-' }}
                </Badge>
              </td>
              <td class="px-3 py-2.5">
                <div v-if="invoice.stripe_payment_link" class="flex items-center gap-1">
                  <span class="text-xs text-muted-foreground font-mono truncate max-w-[130px]">
                    {{ invoice.stripe_payment_link }}
                  </span>
                  <Button variant="ghost" size="icon" class="h-6 w-6 flex-shrink-0" @click="copyLink(invoice.stripe_payment_link, invoice.id)">
                    <Copy v-if="copiedId !== invoice.id" class="w-3 h-3" />
                    <CheckCircle2 v-else class="w-3 h-3 text-green-600" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-6 w-6 flex-shrink-0" @click="openLink(invoice.stripe_payment_link)">
                    <ExternalLink class="w-3 h-3" />
                  </Button>
                </div>
                <span v-else class="text-xs text-muted-foreground">-</span>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ invoice.paid_at ? dayjs(invoice.paid_at).format('YYYY/MM/DD') : '-' }}
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" class="h-7 w-7" title="メール再送" @click="resendEmail(invoice)">
                    <Mail class="w-3.5 h-3.5" />
                  </Button>
                  <Button
                    v-if="invoice.status !== 2"
                    variant="ghost" size="icon"
                    class="h-7 w-7 text-green-600 hover:text-green-700"
                    title="支払済みにする"
                    @click="markAsPaid(invoice)"
                  >
                    <CheckCircle2 class="w-3.5 h-3.5" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ invoices.total }}件</span>
        <Pagination :paginator="invoices" :onPageChange="goPage" />
      </div>
    </div>

    <!-- ========== 検索 Drawer ========== -->
    <Teleport to="body">
      <div v-if="openDrawer" class="fixed inset-0 z-40">
        <div class="absolute inset-0 bg-black/30" @click="openDrawer = false" />
        <aside class="absolute top-0 right-0 h-full w-80 bg-background shadow-xl z-50 flex flex-col">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold">検索</h2>
            <Button variant="ghost" size="icon" @click="openDrawer = false"><X class="w-4 h-4" /></Button>
          </div>
          <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <div class="space-y-1.5">
              <Label>キーワード（契約先名）</Label>
              <Input v-model="form.keyword" placeholder="検索ワードを入力" />
            </div>
            <div class="space-y-1.5">
              <Label>ステータス</Label>
              <Select v-model="form.status">
                <SelectTrigger><SelectValue placeholder="すべて" /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="">すべて</SelectItem>
                  <SelectItem v-for="(label, val) in statusLabels" :key="val" :value="String(val)">{{ label }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          <div class="px-5 py-4 border-t flex gap-2">
            <Button class="flex-1" @click="submitSearch(); openDrawer = false">
              <Search class="w-3.5 h-3.5 mr-1" />検索
            </Button>
            <Button variant="outline" @click="resetSearch">リセット</Button>
          </div>
        </aside>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import {
  Search, X, CreditCard, Mail, Copy, ExternalLink,
  CheckCircle2, Clock, Send, AlertTriangle,
} from 'lucide-vue-next'

import AppLayout  from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SortIcon   from '@/Components/SortIcon.vue'
import { Button }   from '@/components/ui/button'
import { Input }    from '@/components/ui/input'
import { Label }    from '@/components/ui/label'
import { Badge }    from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

// ──────────────────────────────────────────
// Props
// ──────────────────────────────────────────
const props = defineProps({
  invoices: Object,
  filters: {
    type: Object,
    default: () => ({
      keyword: '', status: '', per_page: 20,
      sort_by: 'created_at', sort_dir: 'desc',
    }),
  },
  statusLabels: {
    type: Object,
    default: () => ({ 0: '未送信', 1: '送信済み', 2: '支払済み', 3: 'キャンセル' }),
  },
})

// ──────────────────────────────────────────
// ステータスカード集計
// ──────────────────────────────────────────
const summary = computed(() => {
  const all = props.invoices.data
  return {
    unsent:  all.filter(i => i.status === 0).length,
    sent:    all.filter(i => i.status === 1).length,
    paid:    all.filter(i => i.status === 2).length,
    overdue: all.filter(i =>
      i.status < 2 && i.due_date && dayjs(i.due_date).isBefore(dayjs(), 'day')
    ).length,
  }
})

// ──────────────────────────────────────────
// フォーム
// ──────────────────────────────────────────
const form = reactive({
  keyword:  props.filters.keyword  ?? '',
  status:   props.filters.status   ?? '',
  per_page: props.filters.per_page ?? 20,
  sort_by:  props.filters.sort_by  ?? 'created_at',
  sort_dir: props.filters.sort_dir ?? 'desc',
})

const hasActiveFilters = computed(() => form.keyword || form.status !== '')

// ──────────────────────────────────────────
// 選択
// ──────────────────────────────────────────
const selectedIds = ref([])

const selectAll = computed(() =>
  props.invoices.data.length > 0 &&
  selectedIds.value.length === props.invoices.data.length
)

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.invoices.data.map(i => i.id) : []
}

watch(() => props.invoices.current_page, () => { selectedIds.value = [] })

// ──────────────────────────────────────────
// ユーティリティ
// ──────────────────────────────────────────
const statusVariant = (status) => {
  const map = { 0: 'secondary', 1: 'default', 2: 'outline', 3: 'destructive' }
  return map[status] ?? 'outline'
}

const isOverdue = (invoice) =>
  invoice.status < 2 &&
  invoice.due_date &&
  dayjs(invoice.due_date).isBefore(dayjs(), 'day')

const billingEmail = (invoice) => {
  const addresses = invoice.organization?.addresses ?? []
  for (const type of [3, 2, 1]) {
    const addr = addresses.find(a => a.type === type)
    if (addr?.email) return addr.email
  }
  return ''
}

const openLink = (url) => window.open(url, '_blank')

const copiedId = ref(null)
const copyLink = (url, id) => {
  navigator.clipboard.writeText(url)
  copiedId.value = id
  setTimeout(() => { copiedId.value = null }, 2000)
}

// ──────────────────────────────────────────
// 検索・ソート・ページ
// ──────────────────────────────────────────
const openDrawer = ref(false)

const persistQuery = () => ({
  keyword:  form.keyword,
  status:   form.status,
  per_page: form.per_page,
  sort_by:  form.sort_by,
  sort_dir: form.sort_dir,
  page:     props.invoices.current_page,
})

const submitSearch = () => {
  router.get(route('admin.stripe.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => { selectedIds.value = [] },
  })
}

const resetSearch = () => {
  form.keyword = ''
  form.status  = ''
  submitSearch()
  openDrawer.value = false
}

const goPage = (page) => {
  router.get(route('admin.stripe.index'), { ...persistQuery(), page }, {
    preserveState: true,
    replace: true,
    onSuccess: () => { selectedIds.value = [] },
  })
}

const sortBy = (field) => {
  if (form.sort_by === field) {
    form.sort_dir = form.sort_dir === 'asc' ? 'desc' : 'asc'
  } else {
    form.sort_by  = field
    form.sort_dir = 'desc'
  }
  submitSearch()
}

// ──────────────────────────────────────────
// 支払済みにする（単発）
// ──────────────────────────────────────────
const markAsPaid = (invoice) => {
  if (!confirm(`「${invoice.organization?.name}」を支払済みにしますか？`)) return
  router.patch(route('admin.invoices.update', invoice.id), { status: 2 }, {
    preserveState: true,
  })
}

// 一括支払済み
const bulkMarkAsPaid = () => {
  if (!confirm(`選択した${selectedIds.value.length}件を支払済みにしますか？`)) return
  selectedIds.value.forEach(id => {
    router.patch(route('admin.invoices.update', id), { status: 2 }, { preserveState: true })
  })
  selectedIds.value = []
}

// ──────────────────────────────────────────
// メール再送（単発）
// ──────────────────────────────────────────
const resendEmail = (invoice) => {
  if (!confirm(`「${invoice.organization?.name}」に支払いリンクをメール再送しますか？`)) return
  router.post(route('admin.invoices.resendEmail', invoice.id), {}, { preserveState: true })
}

// 一括再送
const bulkResendEmail = () => {
  if (!confirm(`選択した${selectedIds.value.length}件にメールを再送しますか？`)) return
  selectedIds.value.forEach(id => {
    router.post(route('admin.invoices.resendEmail', id), {}, { preserveState: true })
  })
}

// ──────────────────────────────────────────
// ページネーション
// ──────────────────────────────────────────
const startItem = computed(() =>
  props.invoices.per_page * (props.invoices.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.invoices.per_page * props.invoices.current_page, props.invoices.total)
)
</script>