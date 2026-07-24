<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">ストレージ管理</p>
      <h1 class="text-xl font-semibold">S3ファイル一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- ツールバー -->
      <div class="flex items-center justify-between gap-3">
        <span class="text-sm text-muted-foreground">全{{ files.length }}件</span>
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="openDrawer = true">
            <Search class="w-3.5 h-3.5 mr-1" />検索
          </Button>
        </div>
      </div>

      <!-- 検索中バッジ -->
      <div v-if="hasActiveFilters" class="flex items-center gap-2 flex-wrap">
        <span class="text-xs text-muted-foreground">検索条件:</span>
        <Badge v-if="form.keyword" variant="secondary" class="gap-1">
          キーワード: {{ form.keyword }}
          <button @click="form.keyword = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.directory !== 'all'" variant="secondary" class="gap-1">
          種別: {{ dirLabel(form.directory) }}
          <button @click="form.directory = 'all'; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
        <Badge v-if="form.date_from || form.date_to" variant="secondary" class="gap-1">
          更新日: {{ form.date_from }} 〜 {{ form.date_to }}
          <button @click="form.date_from = ''; form.date_to = ''; submitSearch()"><X class="w-3 h-3" /></button>
        </Badge>
      </div>

      <!-- テーブル -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">種別</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">ファイル名</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">サイズ</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">更新日時</th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-muted-foreground">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="files.length === 0">
              <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">
                <FileText class="w-8 h-8 mx-auto mb-2 opacity-30" />
                ファイルが見つかりません
              </td>
            </tr>
            <tr
              v-for="file in files"
              :key="file.path"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 transition-colors border-b"
            >
              <td class="px-3 py-2.5">
                <Badge :variant="dirVariant(file.directory)">
                  {{ dirLabel(file.directory) }}
                </Badge>
              </td>
              <td class="px-3 py-2.5 font-mono text-xs">{{ file.name }}</td>
              <td class="px-3 py-2.5 text-muted-foreground">{{ formatSize(file.size) }}</td>
              <td class="px-3 py-2.5 text-muted-foreground">{{ file.modified }}</td>
              <td class="px-3 py-2.5 text-center">
                <Button variant="ghost" size="icon" class="h-7 w-7 text-blue-600" title="開く" @click="openPdf(file.url)">
                  <ExternalLink class="w-3.5 h-3.5" />
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- ========== 検索 Drawer ========== -->
    <Teleport to="body">
      <div v-if="openDrawer" class="fixed inset-0 z-40">
        <div class="absolute inset-0 bg-black/30" @click="openDrawer = false" />
        <aside class="absolute top-0 left-64 right-0 bg-background shadow-xl z-50 flex flex-col max-h-[80vh]">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold">検索</h2>
            <Button variant="ghost" size="icon" @click="openDrawer = false">
              <X class="w-4 h-4" />
            </Button>
          </div>
          <div class="overflow-y-auto p-5">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              <div class="space-y-1.5">
                <Label>キーワード</Label>
                <Input v-model="form.keyword" placeholder="ファイル名" />
              </div>
              <div class="space-y-1.5">
                <Label>種別</Label>
                <Select v-model="form.directory">
                  <SelectTrigger><SelectValue placeholder="すべて" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">すべて</SelectItem>
                    <SelectItem value="contracts">契約書</SelectItem>
                    <SelectItem value="invoices">請求書</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-1.5">
                <Label>更新日（開始）</Label>
                <Input v-model="form.date_from" type="date" />
              </div>
              <div class="space-y-1.5">
                <Label>更新日（終了）</Label>
                <Input v-model="form.date_to" type="date" />
              </div>
            </div>
          </div>
          <div class="px-5 py-4 border-t flex gap-2 justify-end">
            <Button size="sm" class="bg-[#0C447C] hover:bg-[#185FA5] text-white" @click="submitSearch(); openDrawer = false">
              <Search class="w-3.5 h-3.5 mr-1" />検索
            </Button>
            <Button variant="outline" @click="resetSearch">リセット</Button>
          </div>
        </aside>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, FileText, ExternalLink, X } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input }  from '@/components/ui/input'
import { Label }  from '@/components/ui/label'
import { Badge }  from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  files:   { type: Array,  default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const openDrawer = ref(false)

const form = reactive({
  keyword:   props.filters.keyword   ?? '',
  directory: props.filters.directory ?? 'all',
  date_from: props.filters.date_from ?? '',
  date_to:   props.filters.date_to   ?? '',
})

const hasActiveFilters = computed(() =>
  form.keyword || form.directory !== 'all' || form.date_from || form.date_to
)

const submitSearch = () => {
  router.get(route('admin.storage.index'), { ...form }, {
    preserveState: true,
    replace: true,
  })
}

const resetSearch = () => {
  form.keyword   = ''
  form.directory = 'all'
  form.date_from = ''
  form.date_to   = ''
  submitSearch()
  openDrawer.value = false
}

const openPdf = (url) => window.open(url, '_blank')

const dirLabel = (dir) => {
  const map = { contracts: '契約書', licenses: 'ライセンス', invoices: '請求書' }
  return map[dir] ?? dir
}

const dirVariant = (dir) => {
  const map = { contracts: 'default', licenses: 'secondary', invoices: 'outline' }
  return map[dir] ?? 'outline'
}

const formatSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1024 / 1024).toFixed(1) + ' MB'
}
</script>