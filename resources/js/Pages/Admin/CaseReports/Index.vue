<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">症例報告管理</p>
      <h1 class="text-xl font-semibold">症例報告一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- フィルター -->
      <div class="flex items-center gap-3 flex-wrap">
        <Select v-model="form.treatment_area" @update:modelValue="submitSearch">
          <SelectTrigger class="w-32 h-9">
            <SelectValue placeholder="治療部位" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">すべて</SelectItem>
            <SelectItem v-for="area in treatmentAreas" :key="area" :value="area">{{ area }}</SelectItem>
          </SelectContent>
        </Select>

        <Select v-model="form.organization_id" @update:modelValue="submitSearch">
          <SelectTrigger class="w-48 h-9">
            <SelectValue placeholder="施設を選択" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">すべての施設</SelectItem>
            <SelectItem v-for="org in organizations" :key="org.id" :value="org.id">
              {{ org.name }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Input v-model="form.submitted_from" type="date" class="h-9 w-40" @change="submitSearch" />
        <span class="text-muted-foreground text-sm">〜</span>
        <Input v-model="form.submitted_to" type="date" class="h-9 w-40" @change="submitSearch" />

        <Button variant="outline" size="sm" @click="resetSearch">リセット</Button>

        <span class="ml-auto text-sm text-muted-foreground">全{{ reports.total }}件</span>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b">
            <tr>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">報告日</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">施設名</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">先生</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">治療部位</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">患者</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">トラブル</th>
              <th class="px-4 py-2.5 text-center text-xs font-semibold text-muted-foreground">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="reports.data.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                症例報告が見つかりません
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
              <td class="px-4 py-2.5 text-sm">
                {{ report.organization?.name ?? report.facility_name_raw ?? '-' }}
              </td>
              <td class="px-4 py-2.5 text-sm">
                {{ report.member?.full_name ?? '-' }}
              </td>
              <td class="px-4 py-2.5">
                <Badge variant="outline">{{ report.treatment_area }}</Badge>
              </td>
              <td class="px-4 py-2.5 text-sm text-muted-foreground">
                {{ report.patient_gender }} / {{ report.patient_age_group }}
              </td>
              <td class="px-4 py-2.5 text-sm text-muted-foreground">
                <span v-if="report.complication_types?.length">
                  {{ report.complication_types[0] }}
                  <span v-if="report.complication_types.length > 1" class="text-xs">
                    他{{ report.complication_types.length - 1 }}件
                  </span>
                </span>
                <span v-else>なし</span>
              </td>
              <td class="px-4 py-2.5 text-center">
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                    <Link :href="route('admin.case-reports.show', report.id)">
                      <Eye class="w-3.5 h-3.5" />
                    </Link>
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-destructive"
                    @click="destroy(report)"
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
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ reports.total }}件</span>
        <Pagination :paginator="reports" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { Eye, Trash2 } from 'lucide-vue-next'
import AppLayout  from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Button } from '@/components/ui/button'
import { Badge }  from '@/components/ui/badge'
import { Input }  from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  reports:        Object,
  organizations:  Array,
  filters:        Object,
  treatmentAreas: Array,
})

const form = reactive({
  treatment_area:  props.filters.treatment_area  ?? 'all',
  organization_id: props.filters.organization_id ?? 'all',
  submitted_from:  props.filters.submitted_from  ?? '',
  submitted_to:    props.filters.submitted_to    ?? '',
})

const submitSearch = () => {
  router.get(route('admin.case-reports.index'), {
    ...form,
    treatment_area:  form.treatment_area  === 'all' ? '' : form.treatment_area,
    organization_id: form.organization_id === 'all' ? '' : form.organization_id,
  }, { preserveState: true, replace: true })
}

const resetSearch = () => {
  form.treatment_area  = 'all'
  form.organization_id = 'all'
  form.submitted_from  = ''
  form.submitted_to    = ''
  submitSearch()
}

const goPage = (page) => {
  router.get(route('admin.case-reports.index'), { ...form, page }, { preserveState: true })
}

const destroy = (report) => {
  if (!confirm('この症例報告を削除しますか？')) return
  router.delete(route('admin.case-reports.destroy', report.id), {
    preserveState: true,
    onSuccess: () => submitSearch(),
  })
}

const startItem = computed(() =>
  props.reports.per_page * (props.reports.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.reports.per_page * props.reports.current_page, props.reports.total)
)
</script>