<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">資料管理</p>
      <h1 class="text-xl font-semibold">資料一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- ツールバー -->
      <div class="flex items-center justify-between">
        <Button variant="outline" size="sm" @click="categoryDialogOpen = true">
          <Tag class="w-3.5 h-3.5 mr-1" />カテゴリー管理
        </Button>
        <Button type="button" size="sm" @click="openUploadDialog">
          <Plus class="w-3.5 h-3.5 mr-1" />資料をアップロード
        </Button>
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

      <!-- 一覧（ドラッグ&ドロップ並べ替え対応） -->
      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b">
            <tr>
              <th class="px-3 py-2.5 w-8"></th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">タイトル</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">グレード</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">ファイル</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">サイズ</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">登録日時</th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold text-muted-foreground w-24">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="currentDocuments.length === 0">
              <td colspan="7" class="px-3 py-12 text-center text-muted-foreground">
                このカテゴリーに資料はありません
              </td>
            </tr>
            <tr
              v-for="(doc, index) in currentDocuments"
              :key="doc.id"
              draggable="true"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors"
              :class="{ 'opacity-40': draggingDocIndex === index }"
              @dragstart="onDocDragStart(index)"
              @dragover.prevent
              @drop="onDocDrop(index)"
              @dragend="draggingDocIndex = null"
            >
              <td class="px-3 py-2.5 text-muted-foreground cursor-grab">
                <GripVertical class="w-4 h-4" />
              </td>
              <td class="px-3 py-2.5">
                <Input
                  v-if="editingId === doc.id"
                  v-model="editForm.title"
                  class="h-8"
                  @keydown.enter="(e) => handleEditDocEnter(e, doc)"
                />
                <span v-else class="font-medium">{{ doc.title }}</span>
              </td>
              <td class="px-3 py-2.5">
                <Select v-if="editingId === doc.id" v-model="editForm.required_tier">
                  <SelectTrigger class="h-8 w-32"><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="(label, tier) in tierLabels" :key="tier" :value="Number(tier)">{{ label }}</SelectItem>
                  </SelectContent>
                </Select>
                <Badge v-else variant="outline" class="text-xs">{{ tierLabels[doc.required_tier] ?? '-' }}以上</Badge>
              </td>
              <td class="px-3 py-2.5 text-xs text-muted-foreground">
                <a :href="doc.file_url" target="_blank" class="hover:underline">
                  {{ doc.original_filename }}
                </a>
              </td>
              <td class="px-3 py-2.5 text-xs text-muted-foreground">
                {{ formatFileSize(doc.file_size) }}
              </td>
              <td class="px-3 py-2.5 text-xs text-muted-foreground">
                {{ doc.created_at }}
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <template v-if="editingId === doc.id">
                    <Button variant="ghost" size="icon" class="h-7 w-7 text-emerald-600" @click="saveEdit(doc)">
                      <Check class="w-3.5 h-3.5" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="editingId = null">
                      <X class="w-3.5 h-3.5" />
                    </Button>
                  </template>
                  <template v-else>
                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="startEdit(doc)">
                      <Pencil class="w-3.5 h-3.5" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive" @click="deleteDocument(doc)">
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

    <!-- ========== 資料アップロード ダイアログ ========== -->
    <Teleport to="body">
      <div v-if="uploadDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeUploadDialog">
        <div class="w-full max-w-lg bg-white rounded-lg shadow-xl overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold text-sm">資料をアップロード</h2>
            <Button variant="ghost" size="icon" class="h-7 w-7" @click="closeUploadDialog">
              <X class="w-4 h-4" />
            </Button>
          </div>

          <div class="p-5 space-y-4">

            <!-- カテゴリー選択＋その場で新規追加 -->
            <div class="space-y-1.5">
              <Label class="text-xs text-muted-foreground">カテゴリー</Label>
              <div class="flex gap-2">
                <Select v-model="uploadForm.category_id" class="flex-1">
                  <SelectTrigger><SelectValue placeholder="カテゴリーを選択" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
                  </SelectContent>
                </Select>
                <Button type="button" variant="outline" size="sm" @click="inlineCategoryFormOpen = !inlineCategoryFormOpen">
                  <Plus class="w-3.5 h-3.5 mr-1" />新規
                </Button>
              </div>

              <!-- カテゴリーその場追加フォーム -->
              <div v-if="inlineCategoryFormOpen" class="flex gap-2 pt-1">
                <Input
                  v-model="newCategoryName"
                  placeholder="新しいカテゴリー名"
                  class="flex-1 h-8"
                  @keydown.enter="(e) => handleAddCategoryEnter(e)"
                />
                <Button type="button" size="sm" class="h-8" :disabled="!newCategoryName.trim()" @click="addCategory">
                  追加
                </Button>
              </div>

              <p v-if="uploadForm.errors.category_id" class="text-xs text-destructive">{{ uploadForm.errors.category_id }}</p>
            </div>

            <!-- タイトル -->
            <div class="space-y-1.5">
              <Label class="text-xs text-muted-foreground">タイトル</Label>
              <Input v-model="uploadForm.title" placeholder="資料のタイトル" />
              <p v-if="uploadForm.errors.title" class="text-xs text-destructive">{{ uploadForm.errors.title }}</p>
            </div>

            <!-- 閲覧可能グレード -->
            <div class="space-y-1.5">
              <Label class="text-xs text-muted-foreground">閲覧可能グレード（このグレード以上の先生が閲覧できます）</Label>
              <Select v-model="uploadForm.required_tier">
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="(label, tier) in tierLabels" :key="tier" :value="Number(tier)">{{ label }}</SelectItem>
                </SelectContent>
              </Select>
              <p v-if="uploadForm.errors.required_tier" class="text-xs text-destructive">{{ uploadForm.errors.required_tier }}</p>
            </div>

            <!-- ドラッグ&ドロップ ファイル選択エリア -->
            <div class="space-y-1.5">
              <Label class="text-xs text-muted-foreground">ファイル（PDF・JPEG・Word、最大50MB）</Label>
              <div
                class="relative flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-8 text-center transition-colors"
                :class="isDragging ? 'border-primary bg-primary/5' : 'border-muted-foreground/25 bg-muted/20'"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
              >
                <UploadCloud class="w-7 h-7 text-muted-foreground" />
                <p class="text-sm text-muted-foreground">
                  ここにPDF・JPEG・Wordをドラッグ＆ドロップ
                  <br />または
                </p>
                <Button type="button" variant="outline" size="sm" @click="fileInput?.click()">
                  ファイルを選択
                </Button>
                <input
                  ref="fileInput"
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.doc,.docx"
                  class="hidden"
                  @change="handleFileSelect"
                />
              </div>

              <!-- 選択中ファイル -->
              <div v-if="uploadForm.document" class="flex items-center justify-between gap-3 rounded-lg border bg-muted/20 px-3 py-2">
                <div class="flex items-center gap-2 min-w-0">
                  <FileText class="w-4 h-4 text-red-600 shrink-0" />
                  <div class="min-w-0">
                    <p class="text-xs font-medium truncate">{{ uploadForm.document.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ formatFileSize(uploadForm.document.size) }}</p>
                  </div>
                </div>
                <Button type="button" variant="ghost" size="icon" class="h-6 w-6 shrink-0" @click="clearFile">
                  <X class="w-3.5 h-3.5" />
                </Button>
              </div>

              <p v-if="uploadForm.errors.document" class="text-xs text-destructive">{{ uploadForm.errors.document }}</p>
            </div>
          </div>

          <div class="px-5 py-4 border-t flex justify-end gap-2">
            <Button type="button" variant="outline" @click="closeUploadDialog">キャンセル</Button>
            <Button type="button" :disabled="!canUpload || uploadForm.processing" @click="upload">
              <UploadCloud class="w-3.5 h-3.5 mr-1" />
              {{ uploadForm.processing ? 'アップロード中...' : 'アップロード' }}
            </Button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ========== カテゴリー管理 ダイアログ ========== -->
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
                  <span class="text-xs text-muted-foreground">（{{ cat.documents_count }}件）</span>
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
                    :class="cat.documents_count > 0 ? 'text-muted-foreground/40 cursor-not-allowed' : 'text-destructive'"
                    :disabled="cat.documents_count > 0"
                    :title="cat.documents_count > 0 ? '資料が登録されているため削除できません' : '削除'"
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
import { UploadCloud, Pencil, Trash2, Check, X, Tag, Plus, GripVertical, FileText } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  documents:  { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  tierLabels: { type: Object, default: () => ({ 1: 'ベーシック', 2: 'アドバンス', 3: 'エキスパート', 4: 'マスター' }) },
})

const activeCategoryId = ref(props.categories[0]?.id ?? null)
const tierLabels = props.tierLabels

function documentsInCategory(categoryId) {
  return props.documents.filter(d => d.category_id === categoryId)
}

const currentDocuments = computed(() => documentsInCategory(activeCategoryId.value))

// ──────────────────────────────────────────
// アップロードダイアログ
// ──────────────────────────────────────────
const uploadDialogOpen = ref(false)
const isDragging = ref(false)
const fileInput = ref(null)
const inlineCategoryFormOpen = ref(false)

const uploadForm = useForm({
  category_id: null,
  title: '',
  required_tier: 1,
  document: null,
})

const canUpload = computed(() => uploadForm.category_id && uploadForm.title.trim() && uploadForm.document)

function openUploadDialog() {
  uploadForm.reset()
  uploadForm.category_id = activeCategoryId.value
  uploadForm.required_tier = 1
  uploadDialogOpen.value = true
  inlineCategoryFormOpen.value = false
}

function closeUploadDialog() {
  uploadDialogOpen.value = false
  clearFile()
}

const ACCEPTED_TYPES = [
  'application/pdf',
  'image/jpeg',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
]

function validateAndSetFile(file) {
  if (!file) return
  if (!ACCEPTED_TYPES.includes(file.type)) {
    uploadForm.setError('document', 'PDF・JPEG画像・Wordファイルのいずれかを選択してください。')
    return
  }
  uploadForm.clearErrors('document')
  uploadForm.document = file
}

function handleFileSelect(e) {
  validateAndSetFile(e.target.files?.[0])
}

function handleDrop(e) {
  isDragging.value = false
  validateAndSetFile(e.dataTransfer?.files?.[0])
}

function clearFile() {
  uploadForm.document = null
  if (fileInput.value) fileInput.value.value = ''
}

function upload() {
  uploadForm.post(route('admin.resource-documents.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      closeUploadDialog()
    },
  })
}

// ──────────────────────────────────────────
// 資料編集（一覧内インライン編集）
// ──────────────────────────────────────────
const editingId = ref(null)
const editForm = reactive({ category_id: null, title: '', required_tier: 1 })

function startEdit(doc) {
  editingId.value = doc.id
  editForm.category_id = doc.category_id
  editForm.title = doc.title
  editForm.required_tier = doc.required_tier
}

function saveEdit(doc) {
  router.put(route('admin.resource-documents.update', doc.id), editForm, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null },
  })
}

function handleEditDocEnter(e, doc) {
  if (e.isComposing || e.keyCode === 229) return
  saveEdit(doc)
}

function deleteDocument(doc) {
  if (!confirm(`「${doc.title}」を削除しますか？`)) return
  router.delete(route('admin.resource-documents.destroy', doc.id), { preserveScroll: true })
}

function formatFileSize(bytes) {
  if (!bytes) return '-'
  const mb = bytes / (1024 * 1024)
  return mb >= 1024 ? `${(mb / 1024).toFixed(2)} GB` : `${mb.toFixed(1)} MB`
}

// ──────────────────────────────────────────
// 資料の並べ替え（同一カテゴリー内のみ）
// ──────────────────────────────────────────
const draggingDocIndex = ref(null)

function onDocDragStart(index) {
  draggingDocIndex.value = index
}

async function onDocDrop(targetIndex) {
  if (draggingDocIndex.value === null || draggingDocIndex.value === targetIndex) return

  const list = [...currentDocuments.value]
  const [moved] = list.splice(draggingDocIndex.value, 1)
  list.splice(targetIndex, 0, moved)
  draggingDocIndex.value = null

  try {
    await axios.post(route('admin.resource-documents.reorder'), {
      ids: list.map(d => d.id),
    })
    router.reload({ only: ['documents'] })
  } catch (e) {
    alert('並び順の更新に失敗しました。')
  }
}

// ──────────────────────────────────────────
// カテゴリー追加（アップロードダイアログ内・カテゴリー管理ダイアログ内 共通）
// ──────────────────────────────────────────
const categoryDialogOpen = ref(false)
const newCategoryName = ref('')

function addCategory() {
  if (!newCategoryName.value.trim()) return
  router.post(route('admin.resource-document-categories.store'), { name: newCategoryName.value }, {
    preserveScroll: true,
    onSuccess: () => {
      newCategoryName.value = ''
      inlineCategoryFormOpen.value = false
    },
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
  router.put(route('admin.resource-document-categories.update', cat.id), { name: editCategoryName.value }, {
    preserveScroll: true,
    onSuccess: () => { editingCategoryId.value = null },
  })
}

function handleEditCategoryEnter(e, cat) {
  if (e.isComposing || e.keyCode === 229) return
  saveCategoryEdit(cat)
}

function deleteCategory(cat) {
  if (cat.documents_count > 0) return
  if (!confirm(`「${cat.name}」を削除しますか？`)) return
  router.delete(route('admin.resource-document-categories.destroy', cat.id), { preserveScroll: true })
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
    await axios.post(route('admin.resource-document-categories.reorder'), {
      ids: list.map(c => c.id),
    })
    router.reload({ only: ['categories'] })
  } catch (e) {
    alert('並び順の更新に失敗しました。')
  }
}
</script>