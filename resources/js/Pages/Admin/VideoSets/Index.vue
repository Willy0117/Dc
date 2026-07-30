<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">動画講習管理</p>
      <h1 class="text-xl font-semibold">動画セット一覧</h1>
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
        </div>

        <div class="flex items-center gap-2">
          <Button size="sm" as-child>
            <Link :href="route('admin.video-sets.create')">
              <Plus class="w-3.5 h-3.5 mr-1" />新規作成
            </Link>
          </Button>
        </div>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="group px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer select-none" @click="sortBy('name')">
                <span class="inline-flex items-center gap-1">
                  セット名
                  <SortChevron field="name" :current="form.sort_by" :dir="form.sort_dir" />
                </span>
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                カテゴリ / テーマ
              </th>
              <th class="group px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer select-none" @click="sortBy('price_jpy')">
                <span class="inline-flex items-center gap-1">
                  価格
                  <SortChevron field="price_jpy" :current="form.sort_by" :dir="form.sort_dir" />
                </span>
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                動画数
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                注文数
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                状態
              </th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                操作
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="videoSets.data.length === 0">
              <td colspan="7" class="px-3 py-12 text-center text-muted-foreground">
                <GraduationCap class="w-8 h-8 mx-auto mb-2 opacity-30" />
                動画セットが見つかりません
              </td>
            </tr>
            <tr
              v-for="vs in videoSets.data"
              :key="vs.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5 font-medium">
                セット{{ vs.name }}
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ vs.category }}<br>
                <span class="text-xs">{{ vs.theme }}</span>
              </td>
              <td class="px-3 py-2.5">
                ¥{{ vs.price_jpy?.toLocaleString() }}
              </td>
              <td class="px-3 py-2.5">
                {{ vs.videos_count }}
              </td>
              <td class="px-3 py-2.5">
                {{ vs.orders_count }}
              </td>
              <td class="px-3 py-2.5">
                <Badge :variant="vs.active ? 'default' : 'secondary'">
                  {{ vs.active ? '公開中' : '非公開' }}
                </Badge>
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                    <Link :href="route('admin.video-sets.edit', vs.id)" title="編集">
                      <Pencil class="w-3.5 h-3.5" />
                    </Link>
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7 text-red-600" title="削除" @click="destroy(vs)">
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
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ videoSets.total }}件</span>
        <Pagination :paginator="videoSets" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Plus, Pencil, Trash2, GraduationCap } from 'lucide-vue-next'

import AppLayout  from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SortChevron from '@/Components/SortChevron.vue'

import { Button } from '@/components/ui/button'
import { Badge }  from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  videoSets: Object,
  filters: {
    type: Object,
    default: () => ({
      per_page: 20,
      sort_by: 'id',
      sort_dir: 'asc',
      page: 1,
    }),
  },
})

const form = reactive({
  per_page: props.filters.per_page ?? 20,
  sort_by:  props.filters.sort_by  ?? 'id',
  sort_dir: props.filters.sort_dir ?? 'asc',
  page:     props.filters.page     ?? 1,
})

const persistQuery = () => ({
  per_page: form.per_page,
  sort_by:  form.sort_by,
  sort_dir: form.sort_dir,
  page:     props.videoSets.current_page,
})

const submitSearch = () => {
  router.get(route('admin.video-sets.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
  })
}

const goPage = (page) => {
  router.get(route('admin.video-sets.index'), { ...persistQuery(), page }, {
    preserveState: true,
    replace: true,
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

const destroy = (vs) => {
  if (!confirm(`「セット${vs.name}」を削除しますか？この操作は取り消せません。`)) return
  router.delete(route('admin.video-sets.destroy', vs.id), {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

const startItem = computed(() =>
  props.videoSets.per_page * (props.videoSets.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.videoSets.per_page * props.videoSets.current_page, props.videoSets.total)
)
</script>
