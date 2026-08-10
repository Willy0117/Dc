<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">アカウント管理</p>
      <h1 class="text-xl font-semibold">プロフィール変更履歴</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- 絞り込み -->
      <div class="flex items-center gap-2">
        <Select v-model="form.target_type" @update:modelValue="submitSearch">
          <SelectTrigger class="w-48 h-9">
            <SelectValue placeholder="種別" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">すべて</SelectItem>
            <SelectItem v-for="(label, key) in targetLabels" :key="key" :value="key">{{ label }}</SelectItem>
          </SelectContent>
        </Select>
        <span class="ml-auto text-sm text-muted-foreground">全{{ logs.total }}件</span>
      </div>

      <!-- 一覧 -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">日時</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">変更したユーザー</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">種別</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">対象</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">変更内容</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="logs.data.length === 0">
              <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">履歴がありません</td>
            </tr>
            <tr
              v-for="log in logs.data"
              :key="log.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors align-top"
            >
              <td class="px-3 py-2.5 text-sm text-muted-foreground whitespace-nowrap">{{ log.created_at }}</td>
              <td class="px-3 py-2.5 text-sm">{{ log.user_name }}</td>
              <td class="px-3 py-2.5">
                <Badge variant="outline">{{ log.target_label }}</Badge>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">{{ log.target_name ?? '-' }}</td>
              <td class="px-3 py-2.5 text-sm">
                <template v-if="log.changes">
                  <ul class="space-y-0.5">
                    <li v-for="(diff, field) in log.changes" :key="field" class="text-xs text-muted-foreground">
                      <span class="font-medium text-foreground">{{ field }}</span>：
                      {{ diff.before || '(空)' }} → {{ diff.after || '(空)' }}
                    </li>
                  </ul>
                </template>
                <span v-else class="text-xs text-muted-foreground">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ logs.total }}件</span>
        <Pagination :paginator="logs" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  logs:         Object,
  targetLabels: { type: Object, default: () => ({}) },
  filters:      { type: Object, default: () => ({}) },
})

const form = reactive({
  target_type: props.filters.target_type ?? 'all',
})

function submitSearch() {
  router.get(route('admin.profile-change-logs.index'), form, {
    preserveState: true,
    replace: true,
  })
}

function goPage(page) {
  router.get(route('admin.profile-change-logs.index'), { ...form, page }, {
    preserveState: true,
    replace: true,
  })
}

const startItem = computed(() => props.logs.per_page * (props.logs.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.logs.per_page * props.logs.current_page, props.logs.total))
</script>
