<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">手技動画管理</p>
      <h1 class="text-xl font-semibold">手技動画一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- ツールバー -->
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <Select v-model="form.organization_id" @update:modelValue="submitSearch">
            <SelectTrigger class="w-56 h-9">
              <SelectValue placeholder="すべての契約先" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">すべての契約先</SelectItem>
              <SelectItem v-for="org in organizationOptions" :key="org.id" :value="org.id">
                {{ org.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 w-16 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                サムネイル
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                タイトル
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                契約先
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                先生
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                サイズ
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                アップロード日時
              </th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                操作
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="videos.data.length === 0">
              <td colspan="7" class="px-3 py-12 text-center text-muted-foreground">
                <Video class="w-8 h-8 mx-auto mb-2 opacity-30" />
                動画が見つかりません
              </td>
            </tr>
            <tr
              v-for="video in videos.data"
              :key="video.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5">
                <a :href="video.file_url" target="_blank"
                  class="flex items-center justify-center w-12 h-12 rounded-lg bg-muted text-muted-foreground hover:bg-muted/70 overflow-hidden">
                  <img v-if="video.thumbnail_url" :src="video.thumbnail_url" class="w-full h-full object-cover" />
                  <Video v-else class="w-5 h-5" />
                </a>
              </td>
              <td class="px-3 py-2.5">
                <a :href="video.file_url" target="_blank" class="font-medium hover:underline">
                  {{ video.title || '(タイトルなし)' }}
                </a>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ video.organization_name ?? '-' }}
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ video.member_name ?? '-' }}
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ formatFileSize(video.file_size) }}
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ video.created_at }}
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive" title="削除" @click="deleteVideo(video)">
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
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ videos.total }}件</span>
        <Pagination :paginator="videos" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Video, Trash2 } from 'lucide-vue-next'

import AppLayout  from '@/Layouts/Admin/AppLayout.vue'
import Pagination  from '@/Components/Pagination.vue'
import { Button }  from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  videos: Object,
  filters: {
    type: Object,
    default: () => ({ organization_id: 'all' }),
  },
  organizationOptions: {
    type: Array,
    default: () => [],
  },
})

const form = reactive({
  organization_id: props.filters.organization_id || 'all',
})

const persistQuery = () => ({
  organization_id: form.organization_id,
})

const submitSearch = () => {
  router.get(route('admin.procedure-videos.index'), persistQuery(), {
    preserveState: true,
    replace: true,
  })
}

const goPage = (page) => {
  router.get(route('admin.procedure-videos.index'), { ...persistQuery(), page }, {
    preserveState: true,
    replace: true,
  })
}

const deleteVideo = (video) => {
  if (!confirm(`「${video.title || '(タイトルなし)'}」を削除しますか？`)) return
  router.delete(route('admin.procedure-videos.destroy', video.id), {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

const formatFileSize = (bytes) => {
  if (!bytes) return '-'
  const mb = bytes / (1024 * 1024)
  return mb >= 1024 ? `${(mb / 1024).toFixed(2)} GB` : `${mb.toFixed(1)} MB`
}

const startItem = computed(() =>
  props.videos.per_page * (props.videos.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.videos.per_page * props.videos.current_page, props.videos.total)
)
</script>