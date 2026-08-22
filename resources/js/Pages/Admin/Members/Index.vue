<template>
  <AppLayout>
    <template #header>会員一覧</template>

    <div class="p-6 space-y-4">

      <!-- ツールバー -->
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <!-- 件数 -->
          <Select v-model="form.per_page" @update:modelValue="submitSearch">
            <SelectTrigger class="w-20 h-9">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</SelectItem>
            </SelectContent>
          </Select>

          <!-- 複数削除 -->
          <Button
            v-if="selectedIds.length > 0"
            variant="destructive"
            size="sm"
            @click="bulkDelete"
          >
            <Trash2 class="w-3.5 h-3.5 mr-1" />
            {{ selectedIds.length }}件削除
          </Button>
        </div>

        <div class="flex items-center gap-2">
          <!-- 検索 -->
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
        <Badge v-if="form.status_id" variant="secondary" class="gap-1">
          状況: {{ statusLabels[form.status_id] }}
          <button @click="form.status_id = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.member_type" variant="secondary" class="gap-1">
          種別: {{ form.member_type }}
          <button @click="form.member_type = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted/50">
            <tr>
              <th class="px-3 py-2.5 w-8">
                <Checkbox :checked="selectAll" @update:checked="toggleSelectAll" />
              </th>
              <th class="px-3 py-2.5 text-left font-medium cursor-pointer hover:text-foreground text-muted-foreground" @click="sortBy('member_number')">
                会員番号
                <SortIcon field="member_number" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left font-medium cursor-pointer hover:text-foreground text-muted-foreground" @click="sortBy('last_name')">
                氏名
                <SortIcon field="last_name" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left font-medium text-muted-foreground">所属</th>
              <th class="px-3 py-2.5 text-left font-medium cursor-pointer hover:text-foreground text-muted-foreground" @click="sortBy('email')">
                メール
                <SortIcon field="email" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left font-medium text-muted-foreground">電話</th>
              <th class="px-3 py-2.5 text-left font-medium cursor-pointer hover:text-foreground text-muted-foreground" @click="sortBy('status_id')">
                状況
                <SortIcon field="status_id" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left font-medium cursor-pointer hover:text-foreground text-muted-foreground" @click="sortBy('joined_at')">
                入会日
                <SortIcon field="joined_at" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-center font-medium text-muted-foreground">操作</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-if="members.data.length === 0">
              <td colspan="9" class="px-3 py-12 text-center text-muted-foreground">
                <Users class="w-8 h-8 mx-auto mb-2 opacity-30" />
                会員が見つかりません
              </td>
            </tr>
            <tr
              v-for="member in members.data"
              :key="member.id"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-3 py-2.5">
                <Checkbox :value="member.id" v-model:checked="selectedIds" />
              </td>
              <td class="px-3 py-2.5 font-mono text-xs text-muted-foreground">
                {{ member.member_number ?? '-' }}
              </td>
              <td class="px-3 py-2.5">
                <Link :href="route('admin.members.show', member.id)" class="font-medium hover:underline">
                  {{ member.full_name ?? '-' }}
                </Link>
                <div v-if="member.full_name_kana" class="text-xs text-muted-foreground">{{ member.full_name_kana }}</div>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ member.organization?.name ?? '-' }}
              </td>
              <td class="px-3 py-2.5 text-sm">{{ member.email ?? '-' }}</td>
              <td class="px-3 py-2.5 text-sm">{{ member.tel ?? '-' }}</td>
              <td class="px-3 py-2.5">
                <Badge :variant="statusVariant(member.status_id)">
                  {{ statusLabels[member.status_id] ?? '-' }}
                </Badge>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ member.joined_at ? dayjs(member.joined_at).format('YYYY/MM/DD') : '-' }}
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                    <Link :href="route('admin.members.edit', { id: member.id, ...persistQuery() })">
                      <Pencil class="w-3.5 h-3.5" />
                    </Link>
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-destructive hover:text-destructive"
                    @click="deleteMember(member)"
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
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ members.total }}件</span>
        <Pagination :paginator="members" :onPageChange="goPage" />
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
              <Label>キーワード（氏名・かな・メール・会員番号）</Label>
              <Input v-model="form.keyword" placeholder="検索ワードを入力" />
            </div>
            <div class="space-y-1.5">
              <Label>会員状況</Label>
              <Select v-model="form.status_id">
                <SelectTrigger><SelectValue placeholder="すべて" /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="">すべて</SelectItem>
                  <SelectItem v-for="(label, id) in statusLabels" :key="id" :value="Number(id)">{{ label }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-1.5">
              <Label>会員種別</Label>
              <Select v-model="form.member_type">
                <SelectTrigger><SelectValue placeholder="すべて" /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="">すべて</SelectItem>
                  <SelectItem value="regular">正会員</SelectItem>
                  <SelectItem value="student">学生会員</SelectItem>
                  <SelectItem value="honorary">名誉会員</SelectItem>
                  <SelectItem value="supporting">賛助会員</SelectItem>
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

    <!-- ========== ステータス変更モーダル ========== -->
    <Dialog v-model:open="showStatusModal">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>会員状況を変更</DialogTitle>
        </DialogHeader>
        <Select v-model="statusForm.status_id">
          <SelectTrigger><SelectValue placeholder="選択してください" /></SelectTrigger>
          <SelectContent>
            <SelectItem v-for="(label, id) in statusLabels" :key="id" :value="Number(id)">{{ label }}</SelectItem>
          </SelectContent>
        </Select>
        <DialogFooter>
          <Button variant="outline" @click="showStatusModal = false">キャンセル</Button>
          <Button @click="submitStatus">更新</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import dayjs from 'dayjs'
import {
  Search, Plus, Trash2, Pencil, Eye, X, Upload, Users
} from 'lucide-vue-next'

import AppLayout      from '@/Layouts/Admin/AppLayout.vue'
import Pagination     from '@/Components/Pagination.vue'
import SortIcon       from '@/Components/SortIcon.vue'
import { Button }     from '@/components/ui/button'
import { Input }      from '@/components/ui/input'
import { Label }      from '@/components/ui/label'
import { Badge }      from '@/components/ui/badge'
import { Checkbox }   from '@/components/ui/checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'

// ──────────────────────────────────────────
// Props
// ──────────────────────────────────────────
const props = defineProps({
  members: Object,
  filters: {
    type: Object,
    default: () => ({
      keyword: '', status_id: '', member_type: '', organization_id: '',
      per_page: 20, sort_by: 'created_at', sort_dir: 'desc',
    }),
  },
  statusLabels: {
    type: Object,
    default: () => ({ 1: '通常', 2: '休会', 3: '退会' }),
  },
})

// ──────────────────────────────────────────
// フォーム
// ──────────────────────────────────────────
const form = reactive({
  keyword:         props.filters.keyword       ?? '',
  status_id:       props.filters.status_id     ?? '',
  member_type:     props.filters.member_type   ?? '',
  organization_id: props.filters.organization_id ?? '',
  per_page:        props.filters.per_page      ?? 20,
  sort_by:         props.filters.sort_by       ?? 'created_at',
  sort_dir:        props.filters.sort_dir      ?? 'desc',
})

const hasActiveFilters = computed(() =>
  form.keyword || form.status_id || form.member_type
)

// ──────────────────────────────────────────
// 選択
// ──────────────────────────────────────────
const selectedIds = ref([])

const selectAll = computed(() =>
  props.members.data.length > 0 &&
  selectedIds.value.length === props.members.data.length
)

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.members.data.map(m => m.id) : []
}

watch(() => props.members.current_page, () => { selectedIds.value = [] })

// ──────────────────────────────────────────
// 検索・ソート・ページ
// ──────────────────────────────────────────
const openDrawer = ref(false)

const persistQuery = () => ({
  keyword:         form.keyword,
  status_id:       form.status_id,
  member_type:     form.member_type,
  organization_id: form.organization_id,
  per_page:        form.per_page,
  sort_by:         form.sort_by,
  sort_dir:        form.sort_dir,
  page:            props.members.current_page,
})

const submitSearch = () => {
  router.get(route('admin.members.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => { selectedIds.value = [] },
  })
}

const resetSearch = () => {
  form.keyword = ''
  form.status_id = ''
  form.member_type = ''
  submitSearch()
  openDrawer.value = false
}

const goPage = (page) => {
  router.get(route('admin.members.index'), { ...persistQuery(), page }, {
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
const deleteMember = (member) => {
  if (!confirm(`「${member.full_name}」を削除しますか？`)) return
  router.delete(route('admin.members.destroy', member.id), {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

const bulkDelete = () => {
  if (!confirm(`選択した${selectedIds.value.length}件を削除しますか？`)) return
  router.post(route('admin.members.bulkDelete'), { ids: selectedIds.value }, {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

// ──────────────────────────────────────────
// ステータス変更
// ──────────────────────────────────────────
const showStatusModal = ref(false)
const statusForm = reactive({ member_id: null, status_id: null })

const openStatus = async (member) => {
  statusForm.member_id = member.id
  statusForm.status_id = member.status_id
  showStatusModal.value = true
}

const submitStatus = async () => {
  await axios.patch(route('admin.members.updateStatus', statusForm.member_id), {
    status_id: statusForm.status_id,
  })
  showStatusModal.value = false
  router.reload({ only: ['members'] })
}

// ──────────────────────────────────────────
// ユーティリティ
// ──────────────────────────────────────────
const statusVariant = (statusId) => {
  const map = { 1: 'default', 2: 'secondary', 3: 'destructive' }
  return map[statusId] ?? 'outline'
}

const startItem = computed(() =>
  props.members.per_page * (props.members.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.members.per_page * props.members.current_page, props.members.total)
)

// Import（将来用）
const openImport = ref(false)
</script>