<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">参考動画管理</p>
      <h1 class="text-xl font-semibold">参考動画一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- カテゴリー管理ボタン -->
      <div class="flex justify-end">
        <Button variant="outline" size="sm" @click="categoryDialogOpen = true">
          <Tag class="w-3.5 h-3.5 mr-1" />カテゴリー管理
        </Button>
      </div>

      <!-- 新規追加フォーム -->
      <div class="border rounded-lg p-4 bg-white space-y-3">
        <h2 class="text-sm font-semibold text-muted-foreground">動画を追加</h2>
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
          <Select v-model="createForm.category_id">
            <SelectTrigger><SelectValue placeholder="カテゴリー" /></SelectTrigger>
            <SelectContent>
              <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
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

      <!-- カテゴリータブ -->
      <div v-if="categories.length > 0" class="flex flex-wrap gap-2">
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          class="px-4 py-1.5 rounded-lg border text-sm font-medium transition-colors"
          :class="activeCategoryId === cat.id
            ? 'bg-primary text-primary-foreground border-primary'
            : 'bg-background hover:bg-muted border-border text-muted-foreground'"
          @click="activeCategoryId = cat.id"
        >
          {{ cat.name }}
          <span class="ml-1 text-xs opacity-70">({{ documentsInCategory(cat.id).length }})</span>
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
                このカテゴリーの動画はありません
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
                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="editingId = null">
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

    <!-- カテゴリー管理ダイアログ -->
    <Teleport to="body">
      <div v-if="categoryDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="categoryDialogOpen = false">
        <div class="w-full max-w-md bg-white rounded-lg shadow-xl overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold text-sm">カテゴリー管理</h2>
            <Button variant="ghost" size="icon" class="h-7 w-7" @click="categoryDialogOpen = false">
              <X class="w-4 h-4" />
            </Button>
          </div>

          <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
            <!-- 新規追加 -->
            <div class="flex gap-2">
              <Input
                v-model="newCategoryName"
                placeholder="新しいカテゴリー名"
                class="flex-1"
                @keydown.enter="(e) => handleAddCategoryEnter(e)"
              />
              <Button type="button" size="sm" :disabled="!newCategoryName.trim()" @click="addCategory">
                <Plus class="w-3.5 h-3.5" />
              </Button>
            </div>

            <!-- 一覧（ドラッグ&ドロップ並べ替え） -->
            <div class="space-y-1">
              <div
                v-for="(cat, index) in categories"
                :key="cat.id"
                draggable="true"
                class="flex items-center gap-2 px-2 py-2 rounded-lg border bg-muted/20 transition-colors"
                :class="{ 'opacity-40': draggingCatIndex === index }"
                @dragstart="onCatDragStart(index)"
                @dragover.prevent
                @drop="onCatDrop(index)"
                @dragend="draggingCatIndex = null"
              >
                <GripVertical class="w-4 h-4 text-muted-foreground cursor-grab shrink-0" />
                <Input
                  v-if="editingCategoryId === cat.id"
                  v-model="editCategoryName"
                  class="h-8 flex-1"
                  @keydown.enter="(e) => handleEditCategoryEnter(e, cat)"
                />
                <span v-else class="flex-1 text-sm">
                  {{ cat.name }}
                  <span class="text-xs text-muted-foreground">（{{ cat.videos_count }}件）</span>
                </span>

                <template v-if="editingCategoryId === cat.id">
                  <Button variant="ghost" size="icon" class="h-7 w-7 text-emerald-600" @click="saveCategoryEdit(cat)">
                    <Check class="w-3.5 h-3.5" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7" @click="editingCategoryId = null">
                    <X class="w-3.5 h-3.5" />
                  </Button>
                </template>
                <template v-else>
                  <Button variant="ghost" size="icon" class="h-7 w-7" @click="startCategoryEdit(cat)">
                    <Pencil class="w-3.5 h-3.5" />
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7"
                    :class="cat.videos_count > 0 ? 'text-muted-foreground/40 cursor-not-allowed' : 'text-destructive'"
                    :disabled="cat.videos_count > 0"
                    :title="cat.videos_count > 0 ? '動画が登録されているため削除できません' : '削除'"
                    @click="deleteCategory(cat)"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </Button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { Plus, GripVertical, Pencil, Trash2, Check, X, Tag } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  videos:     { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
})

const activeCategoryId = ref(props.categories[0]?.id ?? null)

function documentsInCategory(categoryId) {
  return props.videos.filter(v => v.category_id === categoryId)
}

const currentVideos = computed(() => documentsInCategory(activeCategoryId.value))

// ──────────────────────────────────────────
// 新規追加
// ──────────────────────────────────────────
const createForm = reactive({
  category_id: activeCategoryId.value,
  title: '',
  youtube_url: '',
  is_required: false,
})

const canCreate = computed(() =>
  createForm.category_id && createForm.title.trim() && createForm.youtube_url.trim()
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
const editForm = reactive({ category_id: null, title: '', youtube_url: '', is_required: false })

function startEdit(video) {
  editingId.value = video.id
  editForm.category_id = video.category_id
  editForm.title = video.title
  editForm.youtube_url = video.youtube_url
  editForm.is_required = video.is_required
}

function saveEdit(video) {
  router.put(route('admin.reference-videos.update', video.id), editForm, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null },
  })
}

function deleteVideo(video) {
  if (!confirm(`「${video.title}」を削除しますか？`)) return
  router.delete(route('admin.reference-videos.destroy', video.id), { preserveScroll: true })
}

// ──────────────────────────────────────────
// 動画の並べ替え（同一カテゴリー内のみ）
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

// ──────────────────────────────────────────
// カテゴリー管理ダイアログ
// ──────────────────────────────────────────
const categoryDialogOpen = ref(false)
const newCategoryName = ref('')

function addCategory() {
  if (!newCategoryName.value.trim()) return
  router.post(route('admin.reference-video-categories.store'), { name: newCategoryName.value }, {
    preserveScroll: true,
    onSuccess: () => { newCategoryName.value = '' },
  })
}

function handleAddCategoryEnter(e) {
  if (e.isComposing || e.keyCode === 229) return
  addCategory()
}

const editingCategoryId = ref(null)
const editCategoryName = ref('')

function startCategoryEdit(cat) {
  editingCategoryId.value = cat.id
  editCategoryName.value = cat.name
}

function saveCategoryEdit(cat) {
  router.put(route('admin.reference-video-categories.update', cat.id), { name: editCategoryName.value }, {
    preserveScroll: true,
    onSuccess: () => { editingCategoryId.value = null },
  })
}

function handleEditCategoryEnter(e, cat) {
  if (e.isComposing || e.keyCode === 229) return
  saveCategoryEdit(cat)
}

function deleteCategory(cat) {
  if (cat.videos_count > 0) return
  if (!confirm(`「${cat.name}」を削除しますか？`)) return
  router.delete(route('admin.reference-video-categories.destroy', cat.id), { preserveScroll: true })
}

// カテゴリー並べ替え
const draggingCatIndex = ref(null)

function onCatDragStart(index) {
  draggingCatIndex.value = index
}

async function onCatDrop(targetIndex) {
  if (draggingCatIndex.value === null || draggingCatIndex.value === targetIndex) return

  const list = [...props.categories]
  const [moved] = list.splice(draggingCatIndex.value, 1)
  list.splice(targetIndex, 0, moved)
  draggingCatIndex.value = null

  try {
    await axios.post(route('admin.reference-video-categories.reorder'), {
      ids: list.map(c => c.id),
    })
    router.reload({ only: ['categories'] })
  } catch (e) {
    alert('並び順の更新に失敗しました。')
  }
}
</script>
