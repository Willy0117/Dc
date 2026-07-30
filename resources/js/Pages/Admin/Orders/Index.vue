<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">動画講習管理</p>
      <h1 class="text-xl font-semibold">注文一覧</h1>
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

          <Select v-model="form.status" @update:modelValue="submitSearch">
            <SelectTrigger class="w-36 h-9">
              <SelectValue placeholder="状態" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">すべての状態</SelectItem>
              <SelectItem value="paid">購入済み</SelectItem>
              <SelectItem value="pending">保留</SelectItem>
              <SelectItem value="refunded">返金済み</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <Button variant="outline" size="sm" as-child>
          <a :href="route('admin.orders.export')">
            <Download class="w-3.5 h-3.5 mr-1" />CSVダウンロード
          </a>
        </Button>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="group px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer select-none" @click="sortBy('paid_at')">
                <span class="inline-flex items-center gap-1">
                  購入日
                  <SortChevron field="paid_at" :current="form.sort_by" :dir="form.sort_dir" />
                </span>
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                動画セット
              </th>
              <th class="group px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer select-none" @click="sortBy('customer_name')">
                <span class="inline-flex items-center gap-1">
                  購入者
                  <SortChevron field="customer_name" :current="form.sort_by" :dir="form.sort_dir" />
                </span>
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                所属
              </th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                メール
              </th>
              <th class="group px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground cursor-pointer select-none" @click="sortBy('status')">
                <span class="inline-flex items-center gap-1">
                  状態
                  <SortChevron field="status" :current="form.sort_by" :dir="form.sort_dir" />
                </span>
              </th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                詳細
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="orders.data.length === 0">
              <td colspan="7" class="px-3 py-12 text-center text-muted-foreground">
                <Receipt class="w-8 h-8 mx-auto mb-2 opacity-30" />
                注文が見つかりません
              </td>
            </tr>
            <tr
              v-for="order in orders.data"
              :key="order.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ dayjs(order.paid_at ?? order.created_at).format('YYYY/MM/DD HH:mm') }}
              </td>
              <td class="px-3 py-2.5">
                セット{{ order.video_set?.name }}
              </td>
              <td class="px-3 py-2.5 font-medium">
                {{ order.customer_name }}
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ order.affiliation ?? '-' }}
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                {{ order.customer_email }}
              </td>
              <td class="px-3 py-2.5">
                <Badge :variant="order.status === 'paid' ? 'default' : 'secondary'">
                  {{ statusLabel[order.status] ?? order.status }}
                </Badge>
              </td>
              <td class="px-3 py-2.5 text-center">
                <Button variant="ghost" size="icon" class="h-7 w-7" as-child>
                  <Link :href="route('admin.orders.show', order.id)" title="詳細">
                    <Eye class="w-3.5 h-3.5" />
                  </Link>
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div class="flex items-center justify-between text-sm text-muted-foreground">
        <span>{{ startItem }}〜{{ endItem }} 件 / 全{{ orders.total }}件</span>
        <Pagination :paginator="orders" :onPageChange="goPage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { Eye, Receipt, Download } from 'lucide-vue-next'

import AppLayout  from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SortChevron from '@/Components/SortChevron.vue'

import { Button } from '@/components/ui/button'
import { Badge }  from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  orders: Object,
  filters: {
    type: Object,
    default: () => ({
      status: 'all',
      per_page: 20,
      sort_by: 'paid_at',
      sort_dir: 'desc',
      page: 1,
    }),
  },
})

const statusLabel = {
  paid: '購入済み',
  pending: '保留',
  refunded: '返金済み',
}

const form = reactive({
  status:   props.filters.status   ?? 'all',
  per_page: props.filters.per_page ?? 20,
  sort_by:  props.filters.sort_by  ?? 'paid_at',
  sort_dir: props.filters.sort_dir ?? 'desc',
  page:     props.filters.page     ?? 1,
})

const persistQuery = () => ({
  status:   form.status,
  per_page: form.per_page,
  sort_by:  form.sort_by,
  sort_dir: form.sort_dir,
  page:     props.orders.current_page,
})

const submitSearch = () => {
  router.get(route('admin.orders.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
  })
}

const goPage = (page) => {
  router.get(route('admin.orders.index'), { ...persistQuery(), page }, {
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
  props.orders.per_page * (props.orders.current_page - 1) + 1
)
const endItem = computed(() =>
  Math.min(props.orders.per_page * props.orders.current_page, props.orders.total)
)
</script>
