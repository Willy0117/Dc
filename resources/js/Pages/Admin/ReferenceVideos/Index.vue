<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">参考動画管理</p>
      <h1 class="text-xl font-semibold">参考動画一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- 新規追加フォーム -->
      <div class="border rounded-lg p-4 bg-white space-y-3">
        <h2 class="text-sm font-semibold text-muted-foreground">動画を追加</h2>
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
          <Select v-model="createForm.category">
            <SelectTrigger><SelectValue placeholder="カテゴリ" /></SelectTrigger>
            <SelectContent>
              <SelectItem v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</SelectItem>
            </SelectContent>
          </Select>
          <Input v-model="createForm.title" placeholder="タイトル" class="sm:col-span-2" />
          <Input v-model="createForm.youtube_url" placeholder="YouTube URL" />
          <label class="flex items-center gap-2 text-sm px-2">
            <input type="checkbox" v-model="createForm.is_required" class="rounded" />
            必須動画
          </label>
        </div>
        <div class="flex justify-end">
          <Button type="button" size="sm" :disabled="!canCreate" @click="createVideo">
            <Plus class="w-3.5 h-3.5 mr-1" />追加
          </Button>
        </div>
      </div>

      <!-- カテゴリタブ -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="cat in categories"
          :key="cat"
          type="button"
          class="px-4 py-1.5 rounded-lg border text-sm font-medium transition-colors"
          :class="activeCategory === cat
            ? 'bg-primary text-primary-foreground border-primary'
            : 'bg-background hover:bg-muted border-border text-muted-foreground'"
          @click="activeCategory = cat"
        >
          {{ cat }}
          <span class="ml-1 text-xs opacity-70">({{ videosByCategory[cat]?.length ?? 0 }})</span>
        </button>
      </div>

      <!-- 動画一覧（ドラッグ&ドロップ並べ替え） -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b">
            <tr>
              <th class="px-3 py-2.5 w-8"></th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">タイトル</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">URL</th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold text-muted-foreground w-16">必須</th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold text-muted-foreground w-24">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="currentVideos.length === 0">
              <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">
                このカテゴリの動画はありません
              </td>
            </tr>
            <tr
              v-for="(video, index) in currentVideos"
              :key="video.id"
              draggable="true"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors"
              :class="{ 'opacity-40': draggingIndex === index }"
              @dragstart="onDragStart(index)"
              @dragover.prevent
              @drop="onDrop(index)"
              @dragend="draggingIndex = null"
            >
              <td class="px-3 py-2.5 text-muted-foreground cursor-grab">
                <GripVertical class="w-4 h-4" />
              </td>
              <td class="px-3 py-2.5">
                <Input
                  v-if="editingId === video.id"
                  v-model="editForm.title"
                  class="h-8"
                />
                <span v-else class="font-medium">{{ video.title }}</span>
              </td>
              <td class="px-3 py-2.5 text-xs text-muted-foreground">
                <Input
                  v-if="editingId === video.id"
                  v-model="editForm.youtube_url"
                  class="h-8"
                />
                <a v-else :href="video.youtube_url" target="_blank" class="hover:underline break-all">
                  {{ video.youtube_url }}
                </a>
              </td>
              <td class="px-3 py-2.5 text-center">
                <input
                  v-if="editingId === video.id"
                  type="checkbox"
                  v-model="editForm.is_required"
                  class="rounded"
                />
                <Badge v-else-if="video.is_required" variant="destructive" class="text-[10px]">必須</Badge>
                <span v-else class="text-muted-foreground text-xs">-</span>
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <template v-if="editingId === video.id">
                    <Button variant="ghost" size="icon" class="h-7 w-7 text-emerald-600" @click="saveEdit(video)">
                      <Check class="w-3.5 h-3.5" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="cancelEdit">
                      <X class="w-3.5 h-3.5" />
                    </Button>
                  </template>
                  <template v-else>
                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="startEdit(video)">
                      <Pencil class="w-3.5 h-3.5" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive" @click="deleteVideo(video)">
                      <Trash2 class="w-3.5 h-3.5" />
                    </Button>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { Plus, GripVertical, Pencil, Trash2, Check, X } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  videos:     { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
})

const activeCategory = ref(props.categories[0] ?? '全体')

const videosByCategory = computed(() => {
  const map = {}
  for (const cat of props.categories) {
    map[cat] = props.videos.filter(v => v.category === cat)
  }
  return map
})

const currentVideos = computed(() => videosByCategory.value[activeCategory.value] ?? [])

// ──────────────────────────────────────────
// 新規追加
// ──────────────────────────────────────────
const createForm = reactive({
  category: activeCategory.value,
  title: '',
  youtube_url: '',
  is_required: false,
})

const canCreate = computed(() =>
  createForm.category && createForm.title.trim() && createForm.youtube_url.trim()
)

function createVideo() {
  router.post(route('admin.reference-videos.store'), createForm, {
    preserveScroll: true,
    onSuccess: () => {
      createForm.title = ''
      createForm.youtube_url = ''
      createForm.is_required = false
    },
  })
}

// ──────────────────────────────────────────
// 編集
// ──────────────────────────────────────────
const editingId = ref(null)
const editForm = reactive({ category: '', title: '', youtube_url: '', is_required: false })

function startEdit(video) {
  editingId.value = video.id
  editForm.category = video.category
  editForm.title = video.title
  editForm.youtube_url = video.youtube_url
  editForm.is_required = video.is_required
}

function cancelEdit() {
  editingId.value = null
}

function saveEdit(video) {
  router.put(route('admin.reference-videos.update', video.id), editForm, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null },
  })
}

// ──────────────────────────────────────────
// 削除
// ──────────────────────────────────────────
function deleteVideo(video) {
  if (!confirm(`「${video.title}」を削除しますか？`)) return
  router.delete(route('admin.reference-videos.destroy', video.id), { preserveScroll: true })
}

// ──────────────────────────────────────────
// 並べ替え（同一カテゴリ内のみ）
// ──────────────────────────────────────────
const draggingIndex = ref(null)

function onDragStart(index) {
  draggingIndex.value = index
}

async function onDrop(targetIndex) {
  if (draggingIndex.value === null || draggingIndex.value === targetIndex) return

  const list = [...currentVideos.value]
  const [moved] = list.splice(draggingIndex.value, 1)
  list.splice(targetIndex, 0, moved)
  draggingIndex.value = null

  try {
    await axios.post(route('admin.reference-videos.reorder'), {
      ids: list.map(v => v.id),
    })
    router.reload({ only: ['videos'] })
  } catch (e) {
    alert('並び順の更新に失敗しました。')
  }
}
</script>
