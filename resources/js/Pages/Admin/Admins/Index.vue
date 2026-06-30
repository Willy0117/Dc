<template>
  <AppLayout>
    <template #header>{{ t('admin_list') }}</template>
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

          <template v-if="selectedIds.length > 0">
            <Button variant="destructive" size="sm" @click="bulkDelete">
              <Trash2 class="w-3.5 h-3.5 mr-1" />
              {{ selectedIds.length }}件削除
            </Button>
          </template>
        </div>

        <div class="flex items-center gap-2">
          <Button size="sm" as-child>
            <Link :href="route('admin.admins.create', persistQuery())">
              <Plus class="w-3.5 h-3.5 mr-1" />{{ t('add_admin') }}
            </Link>
          </Button>
          <Button variant="outline" size="sm" @click="openDrawer = true">
            <Search class="w-3.5 h-3.5 mr-1" />{{ t('search') }}
          </Button>
        </div>
      </div>

      <!-- 検索中バッジ -->
      <div v-if="hasActiveFilters" class="flex items-center gap-2 flex-wrap">
        <span class="text-xs text-muted-foreground">検索条件:</span>
        <Badge v-if="form.code" variant="secondary" class="gap-1">
          Code: {{ form.code }}
          <button @click="form.code = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.name" variant="secondary" class="gap-1">
          {{ t('name') }}: {{ form.name }}
          <button @click="form.name = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.email" variant="secondary" class="gap-1">
          {{ t('email') }}: {{ form.email }}
          <button @click="form.email = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.tenant_id" variant="secondary" class="gap-1">
          Tenant ID: {{ form.tenant_id }}
          <button @click="form.tenant_id = ''; submitSearch()"><X class="w-3 h-3" /></button>
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
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('name')">
                {{ t('name') }}
                <SortIcon field="name" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer" @click="sortBy('email')">
                {{ t('email') }}
                <SortIcon field="email" :current="form.sort_by" :dir="form.sort_dir" />
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                {{ t('updated_at') }}
              </th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                {{ t('actions') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="admins.data.length === 0">
              <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">
                <UserRoundX class="w-8 h-8 mx-auto mb-2 opacity-30" />
                {{ t('no_results') }}
              </td>
            </tr>
            <tr
              v-for="admin in admins.data"
              :key="admin.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5">
                <Checkbox
                  :model-value="selectedIds.includes(admin.id)"
                  @update:model-value="(checked) => toggleSelect(admin.id, checked)"
                />
              </td>
              <td class="px-3 py-2.5">
                <Link :href="route('admin.admins.edit', { admin: admin.id, ...persistQuery() })" class="font-medium hover:underline">
                  {{ admin.name }}
                </Link>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ admin.email }}
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ admin.updated_at ? dayjs(admin.updated_at).format('YYYY/MM/DD HH:mm:ss') : '' }}
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" class="h-7 w-7" @click="copyAdmin(admin.id)" title="複製">
                    <Copy class="w-3.5 h-3.5" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                    <Link :href="route('admin.admins.edit', { admin: admin.id, ...persistQuery() })">
                      <Pencil class="w-3.5 h-3.5" />
                    </Link>
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-destructive hover:text-destructive"
                    @click="deleteAdmin(admin)"
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
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ admins.total }}件</span>
        <Pagination :paginator="admins" :onPageChange="goPage" />
      </div>
    </div>

    <!-- ========== 検索 Drawer ========== -->
    <Teleport to="body">
      <div v-if="openDrawer" class="fixed inset-0 z-40">
        <div class="absolute inset-0 bg-black/30" @click="openDrawer = false" />
        <aside class="absolute top-0 right-0 h-full w-80 bg-background shadow-xl z-50 flex flex-col">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold">{{ t('search') }}</h2>
            <Button variant="ghost" size="icon" @click="openDrawer = false">
              <X class="w-4 h-4" />
            </Button>
          </div>
          <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <div class="space-y-1.5">
              <Label>Code</Label>
              <Input v-model="form.code" placeholder="Code" />
            </div>
            <div class="space-y-1.5">
              <Label>{{ t('name') }}</Label>
              <Input v-model="form.name" :placeholder="t('name')" />
            </div>
            <div class="space-y-1.5">
              <Label>{{ t('email') }}</Label>
              <Input v-model="form.email" :placeholder="t('email')" />
            </div>
            <div class="space-y-1.5">
              <Label>Tenant ID</Label>
              <Input v-model="form.tenant_id" type="number" placeholder="Tenant ID" />
            </div>
          </div>
          <div class="px-5 py-4 border-t flex gap-2">
            <Button class="flex-1" @click="submitSearch(); openDrawer = false">
              <Search class="w-3.5 h-3.5 mr-1" />{{ t('search') }}
            </Button>
            <Button variant="outline" @click="resetSearch">{{ t('reset') }}</Button>
          </div>
        </aside>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { Search, Plus, Trash2, Pencil, Copy, X, UserRoundX } from 'lucide-vue-next'

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
  admins: Object,
  filters: {
    type: Object,
    default: () => ({
      code: '', name: '', email: '', tenant_id: '',
      per_page: 20, sort_by: 'id', sort_dir: 'desc',
    }),
  },
})

const { t } = useI18n()

// ──────────────────────────────────────────
// フォーム
// ──────────────────────────────────────────
const form = reactive({
  code:      props.filters.code      ?? '',
  name:      props.filters.name      ?? '',
  email:     props.filters.email     ?? '',
  tenant_id: props.filters.tenant_id ?? '',
  per_page:  props.filters.per_page  ?? 20,
  sort_by:   props.filters.sort_by   ?? 'id',
  sort_dir:  props.filters.sort_dir  ?? 'desc',
})

const hasActiveFilters = computed(() =>
  form.code || form.name || form.email || form.tenant_id
)

// ──────────────────────────────────────────
// 選択
// ──────────────────────────────────────────
const selectedIds = ref([])

const selectAll = computed({
  get: () => props.admins.data.length > 0 && selectedIds.value.length === props.admins.data.length,
  set: (checked) => {
    selectedIds.value = checked ? props.admins.data.map((a) => a.id) : []
  },
})

const toggleSelect = (id, checked) => {
  if (checked) {
    if (!selectedIds.value.includes(id)) selectedIds.value.push(id)
  } else {
    selectedIds.value = selectedIds.value.filter((i) => i !== id)
  }
}

watch(() => props.admins.current_page, () => { selectedIds.value = [] })

// ──────────────────────────────────────────
// 検索・ソート・ページ
// ──────────────────────────────────────────
const openDrawer = ref(false)

const persistQuery = () => ({
  code:      form.code,
  name:      form.name,
  email:     form.email,
  tenant_id: form.tenant_id,
  per_page:  form.per_page,
  sort_by:   form.sort_by,
  sort_dir:  form.sort_dir,
  page:      props.admins.current_page,
})

const submitSearch = () => {
  router.get(route('admin.admins.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => { selectedIds.value = [] },
  })
}

const resetSearch = () => {
  form.code = ''
  form.name = ''
  form.email = ''
  form.tenant_id = ''
  submitSearch()
  openDrawer.value = false
}

const goPage = (page) => {
  router.get(route('admin.admins.index'), { ...persistQuery(), page }, {
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
    form.sort_dir = 'asc'
  }
  submitSearch()
}

// ──────────────────────────────────────────
// 削除・複製
// ──────────────────────────────────────────
const deleteAdmin = (admin) => {
  if (!confirm(`「${admin.name}」を削除しますか？`)) return
  router.delete(route('admin.admins.destroy', admin.id), {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

const bulkDelete = () => {
  if (!confirm(`選択した${selectedIds.value.length}件を削除しますか？`)) return
  router.post(route('admin.admins.bulkDelete'), { ids: selectedIds.value }, {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

const copyAdmin = (adminId) => {
  router.get(route('admin.admins.create', { ...persistQuery(), mode: 'copy', admin_id: adminId }))
}

// ──────────────────────────────────────────
// ユーティリティ
// ──────────────────────────────────────────
const startItem = computed(() =>
  props.admins.per_page * (props.admins.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.admins.per_page * props.admins.current_page, props.admins.total)
)
</script>