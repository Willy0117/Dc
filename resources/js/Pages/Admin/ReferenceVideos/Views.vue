<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">参考動画管理</p>
      <h1 class="text-xl font-semibold">視聴状況一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- 種別タブ -->
      <div class="flex gap-2">
        <button
          type="button"
          class="px-4 py-1.5 rounded-lg border text-sm font-medium transition-colors"
          :class="form.user_type === 'organization'
            ? 'bg-primary text-primary-foreground border-primary'
            : 'bg-background text-muted-foreground border-border hover:bg-muted'"
          @click="setUserType('organization')"
        >
          病院（{{ organizationCount }}）
        </button>
        <button
          type="button"
          class="px-4 py-1.5 rounded-lg border text-sm font-medium transition-colors"
          :class="form.user_type === 'member'
            ? 'bg-primary text-primary-foreground border-primary'
            : 'bg-background text-muted-foreground border-border hover:bg-muted'"
          @click="setUserType('member')"
        >
          先生（{{ memberCount }}）
        </button>
      </div>

      <!-- 検索・状況フィルター -->
      <div class="flex items-center gap-3">
        <Input
          v-model="form.keyword"
          placeholder="ユーザー名・契約先名で検索"
          class="w-64 h-9"
          @keyup.enter="submitSearch"
        />
        <Button variant="outline" size="sm" @click="submitSearch">検索</Button>

        <div class="flex gap-1 ml-2">
          <button
            type="button"
            class="px-3 py-1.5 rounded-lg border text-sm transition-colors"
            :class="form.status === 'all' ? 'bg-primary text-primary-foreground border-primary' : 'bg-background text-muted-foreground border-border hover:bg-muted'"
            @click="setStatus('all')"
          >
            すべて
          </button>
          <button
            type="button"
            class="px-3 py-1.5 rounded-lg border text-sm transition-colors"
            :class="form.status === 'incomplete' ? 'bg-destructive text-destructive-foreground border-destructive' : 'bg-background text-muted-foreground border-border hover:bg-muted'"
            @click="setStatus('incomplete')"
          >
            未視聴あり（{{ incompleteCount }}）
          </button>
        </div>
      </div>

      <!-- 一覧（ユーザーごと1行、未視聴タイトルを列挙） -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm table-fixed">
          <colgroup>
            <col class="w-40">
            <col class="w-40">
            <col class="w-24">
            <col>
          </colgroup>
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">契約先</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">ユーザー</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">状況</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">未視聴の動画</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="rows.data.length === 0">
              <td colspan="4" class="px-3 py-12 text-center text-muted-foreground">
                対象ユーザーが見つかりません
              </td>
            </tr>
            <tr
              v-for="row in rows.data"
              :key="row.user_id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors align-top"
            >
              <td class="px-3 py-2.5 text-sm truncate" :title="row.organization_name">
                {{ row.organization_name }}
              </td>
              <td class="px-3 py-2.5 text-sm truncate">
                {{ row.user_name }}
              </td>
              <td class="px-3 py-2.5">
                <Badge v-if="row.is_complete" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-100">
                  <Check class="w-3 h-3 mr-1" />完了
                </Badge>
                <Badge v-else variant="destructive" class="opacity-90">
                  {{ row.completed_count }}/{{ row.total_count }}
                </Badge>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground break-words">
                <span v-if="row.is_complete" class="text-emerald-600">すべて視聴済み</span>
                <ul v-else class="space-y-0.5">
                  <li v-for="title in row.unwatched_titles" :key="title">・{{ title }}</li>
                </ul>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ rows.total }}件</span>
        <Pagination :paginator="rows" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Check } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'

const props = defineProps({
  rows:              { type: Object, required: true },
  organizationCount:  { type: Number, default: 0 },
  memberCount:        { type: Number, default: 0 },
  incompleteCount:    { type: Number, default: 0 },
  filters:            { type: Object, default: () => ({}) },
})

// デフォルトは「病院」
const form = reactive({
  keyword:   props.filters.keyword ?? '',
  user_type: props.filters.user_type ?? 'organization',
  status:    props.filters.status ?? 'all',
})

function submitSearch() {
  router.get(route('admin.reference-videos.views'), { ...form, page: 1 }, {
    preserveState: true,
    replace: true,
  })
}

function setUserType(type) {
  form.user_type = type
  submitSearch()
}

function setStatus(status) {
  form.status = status
  submitSearch()
}

function goPage(page) {
  router.get(route('admin.reference-videos.views'), { ...form, page }, {
    preserveState: true,
    replace: true,
  })
}

const startItem = computed(() =>
  props.rows.per_page * (props.rows.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.rows.per_page * props.rows.current_page, props.rows.total)
)
</script>