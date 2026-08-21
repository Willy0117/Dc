<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">お知らせ管理</p>
      <h1 class="text-xl font-semibold">お知らせ一覧</h1>
    </template>

    <div class="p-6 space-y-4">
      <div class="flex justify-end">
        <Button size="sm" as-child>
          <Link :href="route('admin.notices.create')">
            <Plus class="w-3.5 h-3.5 mr-1" />新規登録
          </Link>
        </Button>
      </div>

      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">タイトル</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">配信対象</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">動画</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">公開日時</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">状態</th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="notices.data.length === 0">
              <td colspan="6" class="px-3 py-12 text-center text-muted-foreground">
                <Megaphone class="w-8 h-8 mx-auto mb-2 opacity-30" />
                お知らせがまだありません
              </td>
            </tr>
            <tr
              v-for="notice in notices.data"
              :key="notice.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5">
                <Link :href="route('admin.notices.edit', notice.id)" class="font-medium hover:underline">
                  {{ notice.title }}
                </Link>
                <p class="text-xs text-muted-foreground truncate max-w-xs">{{ notice.body }}</p>
              </td>
              <td class="px-3 py-2.5 text-sm">{{ targetTypeLabels[notice.target_type] }}</td>
              <td class="px-3 py-2.5">
                <Video v-if="notice.youtube_url" class="w-4 h-4 text-blue-500" />
                <span v-else class="text-muted-foreground text-xs">-</span>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ notice.published_at ? dayjs(notice.published_at).format('YYYY/MM/DD HH:mm') : '-' }}
              </td>
              <td class="px-3 py-2.5">
                <Badge :variant="isPublished(notice) ? 'secondary' : 'outline'">
                  {{ isPublished(notice) ? '公開中' : '下書き' }}
                </Badge>
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" class="h-7 w-7" @click="router.get(route('admin.notices.edit', notice.id))">
                    <Pencil class="w-3.5 h-3.5" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7 text-red-500" @click="destroy(notice)">
                    <Trash2 class="w-3.5 h-3.5" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ notices.total }}件</span>
        <Pagination :paginator="notices" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { Plus, Pencil, Trash2, Video, Megaphone } from 'lucide-vue-next'

import AppLayout  from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'

import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

const props = defineProps({ notices: Object })

const targetTypeLabels = {
  all: '全員(病院・先生)',
  organizations_all: '病院のみ(全病院)',
  members_all: '先生のみ(全先生)',
  organizations: '個別病院',
  members: '個別先生',
}

const isPublished = (notice) => notice.published_at && dayjs(notice.published_at).isBefore(dayjs())

const destroy = (notice) => {
  if (!confirm(`「${notice.title}」を削除しますか？`)) return
  router.delete(route('admin.notices.destroy', notice.id), { preserveState: true })
}

const goPage = (page) => {
  router.get(route('admin.notices.index'), { page }, { preserveState: true, replace: true })
}

const startItem = computed(() =>
  props.notices.per_page * (props.notices.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.notices.per_page * props.notices.current_page, props.notices.total)
)
</script>
