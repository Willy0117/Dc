<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">症例報告</p>
      <h1 class="text-xl font-semibold">症例報告一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- ツールバー -->
      <div class="flex items-center justify-between">
        <span class="text-sm text-muted-foreground">
          全{{ reports.total }}件
        </span>
        <Button size="sm" as-child>
          <Link :href="route('reports.create')">
            <Plus class="w-3.5 h-3.5 mr-1" />新規報告
          </Link>
        </Button>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b">
            <tr>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">報告日</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">先生</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">治療部位</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">患者性別</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">年代</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">トラブル</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="reports.data.length === 0">
              <td colspan="5" class="px-4 py-12 text-center text-muted-foreground">
                症例報告がありません
              </td>
            </tr>
            <tr
              v-for="report in reports.data"
              :key="report.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors"
            >
              <td class="px-4 py-2.5 text-sm">
                {{ report.submitted_at ? dayjs(report.submitted_at).format('YYYY/MM/DD') : '-' }}
              </td>
              <td class="px-4 py-2.5 text-sm">{{ report.member_name ?? '-' }}</td>
              <td class="px-4 py-2.5">
                <Badge variant="outline">{{ report.treatment_area }}</Badge>
              </td>
              <td class="px-4 py-2.5 text-sm">{{ report.patient_gender ?? '-' }}</td>
              <td class="px-4 py-2.5 text-sm">{{ report.patient_age_group ?? '-' }}</td>
              <td class="px-4 py-2.5 text-sm text-muted-foreground">
                <span v-if="report.complication_types?.length">
                  {{ report.complication_types.join('、') }}
                </span>
                <span v-else>なし</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ reports.total }}件</span>
        <Pagination :paginator="reports" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { Plus } from 'lucide-vue-next'
import AppLayout  from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Button } from '@/components/ui/button'
import { Badge }  from '@/components/ui/badge'

const props = defineProps({
  reports: Object,
})

const goPage = (page) => {
  router.get(route('reports.index'), { page }, { preserveState: true })
}

const startItem = computed(() =>
  props.reports.per_page * (props.reports.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.reports.per_page * props.reports.current_page, props.reports.total)
)
</script>
