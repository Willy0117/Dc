<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">動画講習管理</p>
      <h1 class="text-xl font-semibold">発行済みライセンス証</h1>
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
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="group px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer select-none" @click="sortBy('certificate_number')">
                <span class="inline-flex items-center gap-1">
                  証明書番号
                  <SortChevron field="certificate_number" :current="form.sort_by" :dir="form.sort_dir" />
                </span>
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                動画セット / 講義
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                購入者
              </th>
              <th class="group px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer select-none" @click="sortBy('issued_at')">
                <span class="inline-flex items-center gap-1">
                  発行日
                  <SortChevron field="issued_at" :current="form.sort_by" :dir="form.sort_dir" />
                </span>
              </th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                ダウンロード
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="certificates.data.length === 0">
              <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">
                <Award class="w-8 h-8 mx-auto mb-2 opacity-30" />
                発行済みの証明書がありません
              </td>
            </tr>
            <tr
              v-for="cert in certificates.data"
              :key="cert.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5 font-mono text-xs">
                {{ cert.certificate_number }}
              </td>
              <td class="px-3 py-2.5 text-sm">
                セット{{ cert.order?.video_set?.name }}
                <span class="block text-xs text-muted-foreground">{{ cert.video?.title }}</span>
              </td>
              <td class="px-3 py-2.5 text-sm">
                {{ cert.order?.customer_name }}
                <span class="block text-xs text-muted-foreground">{{ cert.order?.customer_email }}</span>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ dayjs(cert.issued_at).format('YYYY/MM/DD') }}
              </td>
              <td class="px-3 py-2.5 text-center">
                <Button variant="outline" size="sm" as-child>
                  <a :href="route('admin.certificates.download', cert.id)">
                    <Download class="w-3.5 h-3.5 mr-1" />PDF
                  </a>
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ certificates.total }}件</span>
        <Pagination :paginator="certificates" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { Award, Download } from 'lucide-vue-next'

import AppLayout  from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SortChevron from '@/Components/SortChevron.vue'

import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  certificates: Object,
  filters: {
    type: Object,
    default: () => ({
      per_page: 20,
      sort_by: 'issued_at',
      sort_dir: 'desc',
      page: 1,
    }),
  },
})

const form = reactive({
  per_page: props.filters.per_page ?? 20,
  sort_by:  props.filters.sort_by  ?? 'issued_at',
  sort_dir: props.filters.sort_dir ?? 'desc',
  page:     props.filters.page     ?? 1,
})

const persistQuery = () => ({
  per_page: form.per_page,
  sort_by:  form.sort_by,
  sort_dir: form.sort_dir,
  page:     props.certificates.current_page,
})

const submitSearch = () => {
  router.get(route('admin.certificates.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
  })
}

const goPage = (page) => {
  router.get(route('admin.certificates.index'), { ...persistQuery(), page }, {
    preserveState: true,
    replace: true,
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

const startItem = computed(() =>
  props.certificates.per_page * (props.certificates.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.certificates.per_page * props.certificates.current_page, props.certificates.total)
)
</script>
