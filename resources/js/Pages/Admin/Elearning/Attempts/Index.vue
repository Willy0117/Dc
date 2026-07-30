<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">e-ラーニング管理</p>
      <h1 class="text-xl font-semibold">受験結果一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- 検索・絞り込み -->
      <div class="flex items-center gap-3">
        <Input v-model="form.keyword" placeholder="先生名・契約先名で検索" class="w-64 h-9" @keyup.enter="submitSearch" />
        <Select v-model="form.organization_id" @update:modelValue="submitSearch">
          <SelectTrigger class="w-56 h-9">
            <SelectValue placeholder="すべての契約先" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">すべての契約先</SelectItem>
            <SelectItem v-for="org in organizationOptions" :key="org.id" :value="org.id">{{ org.name }}</SelectItem>
          </SelectContent>
        </Select>
        <Button variant="outline" size="sm" @click="submitSearch">検索</Button>
      </div>

      <!-- 一覧 -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">受験日時</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">先生</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">契約先</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">得点</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">結果</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="attempts.data.length === 0">
              <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">受験結果が見つかりません</td>
            </tr>
            <tr
              v-for="a in attempts.data"
              :key="a.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors"
            >
              <td class="px-3 py-2.5 text-sm text-muted-foreground">{{ a.submitted_at }}</td>
              <td class="px-3 py-2.5 text-sm font-medium">{{ a.member_name }}</td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">{{ a.organization_name }}</td>
              <td class="px-3 py-2.5 text-sm">{{ a.correct_count }} / {{ a.total_questions }}</td>
              <td class="px-3 py-2.5">
                <Badge v-if="a.is_passed" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-100">合格</Badge>
                <Badge v-else variant="destructive" class="opacity-80">不合格</Badge>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ attempts.total }}件</span>
        <Pagination :paginator="attempts" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  attempts:             Object,
  organizationOptions:  { type: Array, default: () => [] },
  filters:              { type: Object, default: () => ({}) },
})

const form = reactive({
  keyword:         props.filters.keyword ?? '',
  organization_id: props.filters.organization_id ?? 'all',
})

function submitSearch() {
  router.get(route('admin.elearning-attempts.index'), { ...form, page: 1 }, {
    preserveState: true,
    replace: true,
  })
}

function goPage(page) {
  router.get(route('admin.elearning-attempts.index'), { ...form, page }, {
    preserveState: true,
    replace: true,
  })
}

const startItem = computed(() => props.attempts.per_page * (props.attempts.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.attempts.per_page * props.attempts.current_page, props.attempts.total))
</script>
