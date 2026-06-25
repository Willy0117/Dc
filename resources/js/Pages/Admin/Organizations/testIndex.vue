<template>
  <AppLayout>
    <template #header>契約先一覧</template>

    <div class="p-6 space-y-4">

      <!-- ツールバー -->
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <Select v-model="form.per_page" @update:modelValue="submitSearch">
            <SelectTrigger class="w-20 h-9">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</SelectItem>
            </SelectContent>
          </Select>

          <!-- ━━━ 一括操作ボタン群（チェック時に表示） ━━━ -->
          <template v-if="selectedIds.length > -1">
            <Button variant="outline" size="sm" @click="bulkSendInvitation">
              <Mail class="w-3.5 h-3.5 mr-1" />
              {{ selectedIds.length }}件 申込メール送信
            </Button>
            <Button variant="destructive" size="sm" @click="bulkDelete">
              <Trash2 class="w-3.5 h-3.5 mr-1" />
              {{ selectedIds.length }}件削除
            </Button>

            <Button variant="outline" size="sm" @click="openInvoiceForSelected">
              <FileText class="w-3.5 h-3.5 mr-1" />
              {{ selectedIds.length }}件 請求書作成
            </Button>

            <Button variant="outline" size="sm" @click="openStripeForSelected">
              <CreditCard class="w-3.5 h-3.5 mr-1" />
              {{ selectedIds.length }}件 Stripe支払い
            </Button>
          </template>
        </div>

        <div class="flex items-center gap-2">
          <Button size="sm" as-child>
            <Link :href="route('admin.organizations.create')">
              <Plus class="w-3.5 h-3.5 mr-1" />新規登録
            </Link>
          </Button>
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
        <Badge v-if="form.contract_status !== ''" variant="secondary" class="gap-1">
          契約状況: {{ contractStatusLabels[form.contract_status] }}
          <button @click="form.contract_status = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.address1" variant="secondary" class="gap-1">
          都道府県: {{ form.address1 }}
          <button @click="form.address1 = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 w-8">
                <Checkbox
                  :model-value="selectAll"
                  @update:model-value="selectAll = $event"
                />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('contract_no')">
                契約No.
                <SortIcon field="contract_no" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('name')">
                組織名
                <SortIcon field="name" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                所在地
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('tel')">
                電話
                <SortIcon field="tel" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('contract_status')">
                契約状況
                <SortIcon field="contract_status" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('contract_date')">
                契約日 / 次回請求日
                <SortIcon field="contract_date" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                操作
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="organizations.data.length === 0">
              <td colspan="8" class="px-3 py-12 text-center text-muted-foreground">
                <Building2 class="w-8 h-8 mx-auto mb-2 opacity-30" />
                契約先が見つかりません
              </td>
            </tr>
            <tr
              v-for="org in organizations.data"
              :key="org.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5">
                <Checkbox
                  :model-value="selectedIds.includes(org.id)"
                  @update:model-value="(checked) => toggleSelect(org.id, checked)"
                />
              </td>
              <td class="px-3 py-2.5 font-mono text-xs text-muted-foreground">
                {{ org.contract_no ?? '-' }}
              </td>
              <td class="px-3 py-2.5">
                <Link :href="route('admin.organizations.show', org.id)" class="font-medium hover:underline">
                  {{ org.name }}
                </Link>
                <a v-if="org.url" :href="org.url" target="_blank"
                   class="text-xs text-blue-500 hover:underline flex items-center gap-1 mt-0.5">
                  <ExternalLink class="w-3 h-3" />{{ org.url }}
                </a>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                <template v-if="org.location_address">
                  {{ org.location_address.address1 }}{{ org.location_address.address2 }}
                </template>
                <template v-else>-</template>
              </td>
              <td class="px-3 py-2.5 text-sm">
                {{ org.location_address?.tel ?? '-' }}
              </td>
              <td class="px-3 py-2.5">
                <Badge :variant="statusVariant(org.contract_status)">
                  {{ contractStatusLabels[org.contract_status] ?? '-' }}
                </Badge>
              </td>

              <!-- ━━━ 契約日 + 次回請求日 ━━━ -->
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                <div>{{ org.contract_date ? dayjs(org.contract_date).format('YYYY/MM/DD') : '-' }}</div>
                <div v-if="org.contract_date" class="text-xs text-primary font-medium flex items-center gap-1 mt-0.5">
                  <ArrowRight class="w-3 h-3" />
                  {{ nextBillingDate(org.contract_date) }}
                </div>
              </td>

              <!-- ━━━ 操作 ━━━ -->
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-emerald-600 hover:text-emerald-700"
                    title="申込メール送信"
                    @click="sendInvitation(org)"
                  >
                    <Mail class="w-3.5 h-3.5" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                    <Link :href="route('admin.organizations.edit', { id: org.id, ...persistQuery() })">
                      <Pencil class="w-3.5 h-3.5" />
                    </Link>
                  </Button>

                  <!-- 請求書ボタン（単発） -->
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-blue-600 hover:text-blue-700"
                    title="請求書作成"
                    @click="openInvoiceForOne(org)"
                  >
                    <FileText class="w-3.5 h-3.5" />
                  </Button>

                  <!-- Stripe支払いボタン（単発） -->
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-violet-600 hover:text-violet-700"
                    title="Stripe支払い"
                    @click="openStripeForOne(org)"
                  >
                    <CreditCard class="w-3.5 h-3.5" />
                  </Button>

                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-destructive hover:text-destructive"
                    @click="deleteOrganization(org)"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ organizations.total }}件</span>
        <Pagination :paginator="organizations" :onPageChange="goPage" />
      </div>
    </div>

    <!-- ========== 検索 Drawer ========== -->
    <Teleport to="body">
      <div v-if="openDrawer" class="fixed inset-0 z-40">
        <div class="absolute inset-0 bg-black/30" @click="openDrawer = false" />
        <aside class="absolute top-0 right-0 h-full w-80 bg-background shadow-xl z-50 flex flex-col">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold">検索</h2>
            <Button variant="ghost" size="icon" @click="openDrawer = false">
              <X class="w-4 h-4" />
            </Button>
          </div>
          <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <div class="space-y-1.5">
              <Label>キーワード（組織名・略称）</Label>
              <Input v-model="form.keyword" placeholder="検索ワードを入力" />
            </div>
            <div class="space-y-1.5">
              <Label>契約状況</Label>
              <Select v-model="form.contract_status">
                <SelectTrigger><SelectValue placeholder="すべて" /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="">すべて</SelectItem>
                  <SelectItem v-for="(label, id) in contractStatusLabels" :key="id" :value="Number(id)">
                    {{ label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-1.5">
              <Label>都道府県</Label>
              <Select v-model="form.address1">
                <SelectTrigger><SelectValue placeholder="すべて" /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="">すべて</SelectItem>
                  <SelectItem v-for="pref in prefectures" :key="pref" :value="pref">{{ pref }}</SelectItem>
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

    <!-- ========== 請求書 Dialog ========== -->
    <InvoiceDialog
      v-model:open="invoiceDialogOpen"
      :targets="invoiceTargets"
      @done="submitSearch"
    />

    <!-- ========== Stripe支払い Dialog ========== -->
    <StripePaymentDialog
      v-model:open="stripeDialogOpen"
      :targets="stripeTargets"
      @done="submitSearch"
    />

  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import {
  Search, Plus, Trash2, Pencil, Eye, X, Mail,
  Building2, ExternalLink, FileText, CreditCard, ArrowRight,
} from 'lucide-vue-next'

import AppLayout          from '@/Layouts/Admin/AppLayout.vue'
import Pagination         from '@/Components/Pagination.vue'
import SortIcon           from '@/Components/SortIcon.vue'
// ━━━ 自作コンポーネント ━━━
import InvoiceDialog      from '@/Components/InvoiceDialog.vue'
import StripePaymentDialog from '@/Components/StripePaymentDialog.vue'

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
  organizations: Object,
  filters: {
    type: Object,
    default: () => ({
      keyword: '', contract_status: '', address1: '',
      per_page: 20, sort_by: 'contract_date', sort_dir: 'desc',
    }),
  },
  contractStatusLabels: {
    type: Object,
    default: () => ({ 0: '契約中', 1: '契約終了', 2: '特別枠', 3: '個人契約' }),
  },
})

// ──────────────────────────────────────────
// フォーム
// ──────────────────────────────────────────
const form = reactive({
  keyword:         props.filters.keyword         ?? '',
  contract_status: props.filters.contract_status ?? '',
  address1:        props.filters.address1        ?? '',
  per_page:        props.filters.per_page        ?? 20,
  sort_by:         props.filters.sort_by         ?? 'contract_date',
  sort_dir:        props.filters.sort_dir        ?? 'desc',
})

const hasActiveFilters = computed(() =>
  form.keyword || form.contract_status !== '' || form.address1
)

// ──────────────────────────────────────────
// 選択
// ──────────────────────────────────────────
const selectedIds = ref([])

const selectAll = computed({
  get: () => props.organizations.data.length > 0 && selectedIds.value.length === props.organizations.data.length,
  set: (checked) => {
    selectedIds.value = checked ? props.organizations.data.map(o => o.id) : []
  }
})


const toggleSelect = (id, checked) => {
  if (checked) {
    if (!selectedIds.value.includes(id)) {
      selectedIds.value.push(id)
    }
  } else {
    selectedIds.value = selectedIds.value.filter(i => i !== id)
  }
}

watch(() => props.organizations.current_page, () => { selectedIds.value = [] })

// ──────────────────────────────────────────
// 年次請求日
// ──────────────────────────────────────────
const nextBillingDate = (contractDate) => {
  if (!contractDate) return '-'
  return dayjs(contractDate).add(1, 'year').format('YYYY/MM/DD')
}

// ──────────────────────────────────────────
// 請求書 Dialog
// ──────────────────────────────────────────
const invoiceDialogOpen = ref(false)
const invoiceTargets    = ref([])

const openInvoiceForOne = (org) => {
  invoiceTargets.value    = [org]
  invoiceDialogOpen.value = true
}

const openInvoiceForSelected = () => {
  invoiceTargets.value    = props.organizations.data.filter(o => selectedIds.value.includes(o.id))
  invoiceDialogOpen.value = true
}

// ──────────────────────────────────────────
// Stripe 支払い Dialog
// ──────────────────────────────────────────
const stripeDialogOpen = ref(false)
const stripeTargets    = ref([])

const openStripeForOne = (org) => {
  stripeTargets.value    = [org]
  stripeDialogOpen.value = true
}

const openStripeForSelected = () => {
  stripeTargets.value    = props.organizations.data.filter(o => selectedIds.value.includes(o.id))
  stripeDialogOpen.value = true
}

// ──────────────────────────────────────────
// 検索・ソート・ページ
// ──────────────────────────────────────────
const openDrawer = ref(false)

const persistQuery = () => ({
  keyword:         form.keyword,
  contract_status: form.contract_status,
  address1:        form.address1,
  per_page:        form.per_page,
  sort_by:         form.sort_by,
  sort_dir:        form.sort_dir,
  page:            props.organizations.current_page,
})

const submitSearch = () => {
  router.get(route('admin.organizations.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => { selectedIds.value = [] },
  })
}

const resetSearch = () => {
  form.keyword = ''
  form.contract_status = ''
  form.address1 = ''
  submitSearch()
  openDrawer.value = false
}

const goPage = (page) => {
  router.get(route('admin.organizations.index'), { ...persistQuery(), page }, {
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
// 削除
// ──────────────────────────────────────────
const deleteOrganization = (org) => {
  if (!confirm(`「${org.name}」を削除しますか？`)) return
  router.delete(route('admin.organizations.destroy', org.id), {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

const bulkDelete = () => {
  if (!confirm(`選択した${selectedIds.value.length}件を削除しますか？`)) return
  router.post(route('admin.organizations.bulkDelete'), { ids: selectedIds.value }, {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

// ──────────────────────────────────────────
// ユーティリティ
// ──────────────────────────────────────────
const statusVariant = (status) => {
  const map = { 0: 'default', 1: 'secondary', 2: 'outline', 3: 'destructive' }
  return map[status] ?? 'outline'
}

const startItem = computed(() =>
  props.organizations.per_page * (props.organizations.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.organizations.per_page * props.organizations.current_page, props.organizations.total)
)

// ──────────────────────────────────────────
// 都道府県リスト
// ──────────────────────────────────────────
const prefectures = [
  '北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県',
  '茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県',
  '新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県',
  '静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県',
  '奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県',
  '徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県',
  '熊本県','大分県','宮崎県','鹿児島県','沖縄県',
]

// ──────────────────────────────────────────
// 申込メール送信
// ──────────────────────────────────────────
const sendInvitation = (org) => {
  if (!confirm(`「${org.name}」に申込メールを送信しますか？`)) return
  router.post(route('admin.organizations.send-invitation', org.id), {}, {
    preserveState: true,
    onSuccess: () => alert('送信しました。'),
  })
}

const bulkSendInvitation = () => {
  if (!confirm(`選択した${selectedIds.value.length}件に申込メールを送信しますか？`)) return
  router.post(route('admin.organizations.bulk-send-invitation'), { ids: selectedIds.value }, {
    preserveState: true,
    onSuccess: () => {
      alert('送信しました。')
      selectedIds.value = []
    },
  })
}

</script>