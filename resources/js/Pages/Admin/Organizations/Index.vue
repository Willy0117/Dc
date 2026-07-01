<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">契約先管理</p>
      <h1 class="text-xl font-semibold">契約先一覧</h1>
    </template>

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
          <template v-if="selectedIds.length > 0">
            <Button variant="outline" size="sm" @click="bulkMailDialogOpen = true">
              <Mail class="w-3.5 h-3.5 mr-1" />
              {{ selectedIds.length }}件 メール送信
            </Button>
            <Button variant="outline" size="sm" @click="bulkSendReminder">
              <Bell class="w-3.5 h-3.5 mr-1" />
              {{ selectedIds.length }}件 リマインダー送信
            </Button>
            <!-- 以下将来用コメントアウト -->
            <!--
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
            -->
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
        <Badge v-if="form.contract_date_from || form.contract_date_to" variant="secondary" class="gap-1">
          契約日: {{ form.contract_date_from }} 〜 {{ form.contract_date_to }}
          <button @click="form.contract_date_from = ''; form.contract_date_to = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.payment_method !== 'all' && form.payment_method !== ''" variant="secondary" class="gap-1">
          支払方法: {{ form.payment_method == 1 ? '銀行振込' : 'カード' }}
          <button @click="form.payment_method = 'all'; submitSearch()"><X class="w-3 h-3" /></button>
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
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                支払方法
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
              <td colspan="9" class="px-3 py-12 text-center text-muted-foreground">
                <Building2 class="w-8 h-8 mx-auto mb-2 opacity-30" />
                契約先が見つかりません
              </td>
            </tr>
            <tr
              v-for="org in organizations.data"
              :key="org.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
              :class="getContractBarColor(org)"
            >
              <td class="px-3 py-2.5">
                <Checkbox
                  :model-value="selectedIds.includes(org.id)"
                  @update:model-value="(checked) => toggleSelect(org.id, checked)"
                />
              </td>
              <td class="px-3 py-2.5 font-mono text-xs text-muted-foreground">
                {{ org.code ?? '-' }}
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
              <td class="px-3 py-2.5">
                <span class="flex items-center gap-1 text-xs">
                  <template v-if="org.payment_method === 1">
                    <Building2 class="w-3.5 h-3.5 text-muted-foreground" />
                    <span class="text-muted-foreground">銀行振込</span>
                  </template>
                  <template v-else-if="org.payment_method === 2">
                    <CreditCard class="w-3.5 h-3.5 text-violet-600" />
                    <span class="text-violet-600">カード</span>
                  </template>
                  <template v-else>-</template>
                </span>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                <div>{{ org.contract_date ? dayjs(org.contract_date).format('YYYY/MM/DD') : '-' }}</div>
                <div v-if="org.new_contract_date" class="text-xs text-primary font-medium flex items-center gap-1 mt-0.5">
                  <ArrowRight class="w-3 h-3" />
                  {{ dayjs(org.new_contract_date).format('YYYY/MM/DD') }}
                  <span class="text-muted-foreground font-normal">
                    (あと{{ dayjs(org.new_contract_date).diff(dayjs(), 'day') }}日)
                  </span>
                </div>
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <!-- よく使うボタン -->
                  <Button variant="ghost" size="icon" class="h-7 w-7 text-emerald-600" title="申込メール送信" @click="sendInvitation(org)">
                    <Mail class="w-3.5 h-3.5" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7" @click="openEdit(org)">
                    <Pencil class="w-3.5 h-3.5" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7 text-blue-600" title="請求書作成" @click="openInvoiceForOne(org)">
                    <FileText class="w-3.5 h-3.5" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7 text-violet-600" title="Stripe支払い" @click="openStripeForOne(org)">
                    <CreditCard class="w-3.5 h-3.5" />
                  </Button>

                  <!-- たまに使う → ドロップダウン -->
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="icon" class="h-7 w-7">
                        <MoreHorizontal class="w-3.5 h-3.5" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem @click="issueLicense(org)">
                        <Award class="w-3.5 h-3.5 mr-2 text-amber-600" />
                        ライセンス証発行
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="openContractDialog(org)">
                        <FileText class="w-3.5 h-3.5 mr-2 text-blue-600" />
                        契約書閲覧
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
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
        <aside class="absolute top-0 left-64 right-0 bg-background shadow-xl z-50 flex flex-col max-h-[80vh]">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold">検索</h2>
            <Button variant="ghost" size="icon" @click="openDrawer = false">
              <X class="w-4 h-4" />
            </Button>
          </div>
          <div class="overflow-y-auto p-5">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              <div class="space-y-1.5">
                <Label>キーワード</Label>
                <Input v-model="form.keyword" placeholder="組織名・略称" />
              </div>
              <div class="space-y-1.5">
                <Label>契約状況</Label>
                <Select v-model="form.contract_status">
                  <SelectTrigger><SelectValue placeholder="すべて" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">すべて</SelectItem>
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
                    <SelectItem value="all">すべて</SelectItem>
                    <SelectItem v-for="pref in prefectures" :key="pref" :value="pref">{{ pref }}</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <!-- ━━━ 支払方法（新規追加） ━━━ -->
              <div class="space-y-1.5">
                <Label>支払方法</Label>
                <Select v-model="form.payment_method">
                  <SelectTrigger><SelectValue placeholder="すべて" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">すべて</SelectItem>
                    <SelectItem :value="1">銀行振込</SelectItem>
                    <SelectItem :value="2">カード</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-1.5">
                <Label>契約日（開始）</Label>
                <Input v-model="form.contract_date_from" type="date" />
              </div>
              <div class="space-y-1.5">
                <Label>契約日（終了）</Label>
                <Input v-model="form.contract_date_to" type="date" />
              </div>
            </div>
          </div>
          <div class="px-5 py-4 border-t flex gap-2 justify-end">
            <Button size="sm" variant="outline" class="bg-[#0C447C] hover:bg-[#185FA5] text-white border-[#0C447C]" @click="submitSearch(); openDrawer = false">
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

    <!-- ========== 招待メール送信 Dialog ========== -->
    <InvitationDialog
      v-model:open="invitationDialogOpen"
      :organization="invitationTarget"
      @done="submitSearch"
    />

    <!-- ========== ライセンス証メール送信 Dialog ========== -->
    <LicenseDialog
      v-model:open="licenseDialogOpen"
      :org="currentLicenseOrg"
      :pdf-url="licensePdfUrl"
      @mail="mailLicense"
    />

    <!-- ========== 契約書閲覧 Dialog ========== -->
    <ContractDialog
      v-model:open="contractDialogOpen"
      :org="contractDialogOrg"
    />

    <!-- ========== 一括メール送信 Dialog（新規追加） ========== -->
    <BulkMailDialog
      v-model:open="bulkMailDialogOpen"
      :targets="bulkMailTargets"
      @done="submitSearch"
    />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import {
  Search, Plus, Trash2, Pencil, X, Mail, Award, Bell,
  Building2, ExternalLink, FileText, CreditCard, ArrowRight, MoreHorizontal
} from 'lucide-vue-next'

import AppLayout           from '@/Layouts/Admin/AppLayout.vue'
import Pagination          from '@/Components/Pagination.vue'
import SortIcon            from '@/Components/SortIcon.vue'
import InvoiceDialog       from '@/Components/InvoiceDialog.vue'
import StripePaymentDialog from '@/Components/StripePaymentDialog.vue'
import InvitationDialog    from '@/Components/InvitationDialog.vue'
import LicenseDialog       from '@/Components/LicenseDialog.vue'
import ContractDialog      from '@/Components/ContractDialog.vue'
import BulkMailDialog      from '@/Components/BulkMailDialog.vue'  // ← 新規追加

import { Button }   from '@/components/ui/button'
import { Input }    from '@/components/ui/input'
import { Label }    from '@/components/ui/label'
import { Badge }    from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, } from '@/components/ui/dropdown-menu'

// ──────────────────────────────────────────
// Props
// ──────────────────────────────────────────
const props = defineProps({
  organizations: Object,
  filters: {
    type: Object,
    default: () => ({
      keyword:            '',
      contract_status:    '',
      address1:           '',
      contract_date_from: '',
      contract_date_to:   '',
      payment_method:     'all',
      per_page:           20,
      sort_by:            'contract_date',
      sort_dir:           'desc',
      page:               1,
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
  keyword:            props.filters.keyword            ?? '',
  contract_status:    props.filters.contract_status    ?? 'all',
  address1:           props.filters.address1           ?? 'all',
  contract_date_from: props.filters.contract_date_from ?? '',
  contract_date_to:   props.filters.contract_date_to   ?? '',
  payment_method: props.filters.payment_method && props.filters.payment_method !== 'all'
  ? Number(props.filters.payment_method)
  : 'all',
  per_page:           props.filters.per_page           ?? 20,
  sort_by:            props.filters.sort_by            ?? 'contract_date',
  sort_dir:           props.filters.sort_dir           ?? 'desc',
  page:               props.filters.page               ?? 1,
})

const hasActiveFilters = computed(() =>
  form.keyword || form.contract_status !== '' || form.address1 ||
  form.contract_date_from || form.contract_date_to ||
  (form.payment_method !== 'all' && form.payment_method !== '')
)

const openEdit = (org) => {
  router.get(
    route('admin.organizations.edit', { organization: org.id }),
    persistQuery()
  )
}

// ──────────────────────────────────────────
// 選択
// ──────────────────────────────────────────
const selectedIds = ref([])

const selectAll = computed({
  get: () => props.organizations.data.length > 0 && selectedIds.value.length === props.organizations.data.length,
  set: (checked) => {
    selectedIds.value = checked ? props.organizations.data.map(o => o.id) : []
  },
})

const toggleSelect = (id, checked) => {
  if (checked) {
    if (!selectedIds.value.includes(id)) selectedIds.value.push(id)
  } else {
    selectedIds.value = selectedIds.value.filter(i => i !== id)
  }
}

watch(() => props.organizations.current_page, () => { selectedIds.value = [] })

// ──────────────────────────────────────────
// 請求書 Dialog
// ──────────────────────────────────────────
const invoiceDialogOpen = ref(false)
const invoiceTargets    = ref([])

const openInvoiceForOne = (org) => {
  invoiceTargets.value    = [org]
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

// ──────────────────────────────────────────
// 検索・ソート・ページ
// ──────────────────────────────────────────
const openDrawer = ref(false)

const persistQuery = () => ({
  keyword:            form.keyword,
  contract_status:    form.contract_status,
  address1:           form.address1,
  contract_date_from: form.contract_date_from,
  contract_date_to:   form.contract_date_to,
  payment_method:     form.payment_method,  // ← 追加
  per_page:           form.per_page,
  sort_by:            form.sort_by,
  sort_dir:           form.sort_dir,
  page:               props.organizations.current_page,
})

const submitSearch = () => {
  router.get(route('admin.organizations.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => { selectedIds.value = [] },
  })
}

const resetSearch = () => {
  form.keyword            = ''
  form.contract_status    = ''
  form.address1           = ''
  form.contract_date_from = ''
  form.contract_date_to   = ''
  form.payment_method     = 'all'
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
// 削除（将来用）
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
// 申込メール送信 Dialog
// ──────────────────────────────────────────
const invitationDialogOpen = ref(false)
const invitationTarget     = ref(null)

const sendInvitation = (org) => {
  invitationTarget.value     = org
  invitationDialogOpen.value = true
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
// ──────────────────────────────────────────
// リマインダーメール送信（複数・新規追加）
// ──────────────────────────────────────────
const bulkSendReminder = () => {
  const targets = props.organizations.data.filter(o => selectedIds.value.includes(o.id))
  const noDate  = targets.filter(o => !o.new_contract_date)

  if (noDate.length > 0) {
    alert(`次回契約日が未設定の組織が${noDate.length}件あります。\n（${noDate.map(o => o.name).join('、')}）\n設定済みの組織のみ送信します。`)
  }

  const validIds = targets.filter(o => o.new_contract_date).map(o => o.id)
  if (validIds.length === 0) {
    alert('送信対象がありません。次回契約日を設定してください。')
    return
  }

  if (!confirm(`${validIds.length}件にリマインダーメールを送信しますか？`)) return

  router.post(route('admin.organizations.bulk-send-reminder'), { ids: validIds }, {
    preserveState: true,
    onSuccess: () => {
      alert('送信しました。')
      selectedIds.value = []
    },
  })
}

// ──────────────────────────────────────────
// 一括メール送信 Dialog（新規追加）
// ──────────────────────────────────────────
const bulkMailDialogOpen = ref(false)
const bulkMailTargets    = computed(() =>
  props.organizations.data.filter(o => selectedIds.value.includes(o.id))
)

// ──────────────────────────────────────────
// ライセンス証 Dialog
// ──────────────────────────────────────────
const licenseDialogOpen = ref(false)
const currentLicenseOrg = ref(null)
const licensePdfUrl     = ref(null)

const issueLicense = async (org) => {
  currentLicenseOrg.value = org
  try {
    const response = await axios.post(route('admin.organizations.license', { id: org.id }), {
      display_name: org.name,
    })
    licensePdfUrl.value     = response.data.url
    licenseDialogOpen.value = true
  } catch (e) {
    console.error(e.response?.data?.message)
  }
}

// ──────────────────────────────────────────
// ライセンス証メール送信
// ──────────────────────────────────────────
const mailLicense = async ({ email, pdfPath }) => {
  try {
    await axios.post(route('admin.organizations.license.mail', { id: currentLicenseOrg.value.id }), {
      email,
      pdf_path: pdfPath,
    })
    alert('メールを送付しました。')
    licenseDialogOpen.value = false
  } catch (e) {
    alert(e.response?.data?.message ?? 'メール送信に失敗しました。')
  }
}

// ──────────────────────────────────────────
// 契約書閲覧 Dialog
// ──────────────────────────────────────────
const contractDialogOpen = ref(false)
const contractDialogOrg  = ref(null)

const openContractDialog = (org) => {
  contractDialogOrg.value  = org
  contractDialogOpen.value = true
}

const getContractBarColor = (org) => {
  // new_contract_dateがあればそれを使う、なければcontract_date + 1年
  let renewalDate = null

  if (org.new_contract_date) {
    renewalDate = dayjs(org.new_contract_date)
  } else if (org.contract_date) {
    renewalDate = dayjs(org.contract_date).add(1, 'year')
  } else {
    return '' // どちらもなければバーなし
  }

  const daysUntil = renewalDate.diff(dayjs(), 'day')

  if (daysUntil < 0) return 'border-l-4 border-l-red-500'       // 期限超過
  if (daysUntil <= 45) return 'border-l-4 border-l-orange-400'  // 45日以内
  return ''                                                       // 通常
}
</script>