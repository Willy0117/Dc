<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">フォーム管理</p>
      <h1 class="text-xl font-semibold">フォーム項目管理</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- 治療部位タブ -->
      <div class="flex items-center gap-1 border-b flex-wrap">
        <button
          v-for="area in treatmentAreas"
          :key="area"
          @click="switchArea(area)"
          class="px-4 py-2 text-sm font-medium transition-colors border-b-2 -mb-px"
          :class="currentArea === area
            ? 'border-primary text-primary'
            : 'border-transparent text-muted-foreground hover:text-foreground'"
        >
          {{ area }}
          <span v-if="categoryTierLabel(area)" class="ml-1 text-[10px] text-muted-foreground">
            ({{ categoryTierLabel(area) }}以上)
          </span>
        </button>
        <button
          type="button"
          class="px-3 py-2 text-sm text-muted-foreground hover:text-primary shrink-0"
          @click="addCategoryDialogOpen = true"
          title="カテゴリーを追加"
        >
          <Plus class="w-4 h-4" />
        </button>
      </div>

      <!-- 質問項目追加ボタン -->
      <div class="flex justify-end">
        <Button size="sm" @click="addFieldDialogOpen = true">
          <Plus class="w-3.5 h-3.5 mr-1" />質問項目を追加
        </Button>
      </div>

      <!-- 質問項目一覧 -->
      <div class="space-y-4">
        <div
          v-for="field in fields"
          :key="field.id"
          class="border rounded-lg overflow-hidden"
          :class="!field.is_active ? 'opacity-50' : ''"
        >
          <!-- 質問項目ヘッダー -->
          <div class="bg-muted px-4 py-2.5 border-b flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold">{{ field.field_name }}</span>
              <Badge variant="outline" class="text-xs">{{ field.field_type }}</Badge>
            </div>
            <div class="flex items-center gap-1">
              <Button size="sm" variant="outline" @click="openAddOption(field)">
                <Plus class="w-3.5 h-3.5 mr-1" />選択肢追加
              </Button>
              <Button variant="ghost" size="icon" class="h-7 w-7" @click="toggleField(field)">
                <Eye v-if="field.is_active" class="w-3.5 h-3.5" />
                <EyeOff v-else class="w-3.5 h-3.5" />
              </Button>
              <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive" @click="destroyField(field)">
                <Trash2 class="w-3.5 h-3.5" />
              </Button>
            </div>
          </div>

          <!-- 選択肢一覧 -->
          <table class="w-full text-sm">
            <tbody>
              <tr v-if="field.options.length === 0">
                <td class="px-4 py-3 text-center text-muted-foreground">選択肢がありません</td>
              </tr>
              <tr
                v-for="option in field.options"
                :key="option.id"
                class="odd:bg-white even:bg-muted/30 border-b"
                :class="!option.is_active ? 'opacity-40' : ''"
              >
                <td class="px-4 py-2.5 text-sm">{{ option.label }}</td>
                <td class="px-4 py-2.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="toggleOption(option)">
                      <Eye v-if="option.is_active" class="w-3.5 h-3.5" />
                      <EyeOff v-else class="w-3.5 h-3.5" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive" @click="destroyOption(option)">
                      <Trash2 class="w-3.5 h-3.5" />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 質問項目追加 Dialog -->
    <Dialog v-model:open="addFieldDialogOpen">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>質問項目を追加</DialogTitle>
          <DialogDescription>{{ currentArea }}</DialogDescription>
        </DialogHeader>
        <div class="space-y-3 py-2">
          <div class="space-y-1.5">
            <Label>質問名 <span class="text-destructive">*</span></Label>
            <Input v-model="newField.field_name" placeholder="例：治療回数" />
          </div>
          <div class="space-y-1.5">
            <Label>入力タイプ <span class="text-destructive">*</span></Label>
            <Select v-model="newField.field_type">
              <SelectTrigger><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="checkbox">チェックボックス（複数選択）</SelectItem>
                <SelectItem value="radio">ラジオボタン（単一選択）</SelectItem>
                <SelectItem value="text">テキスト入力</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        <div class="flex justify-end gap-2">
          <Button variant="outline" @click="addFieldDialogOpen = false">キャンセル</Button>
          <Button @click="submitAddField" :disabled="!newField.field_name">追加</Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- 選択肢追加 Dialog -->
    <Dialog v-model:open="addOptionDialogOpen">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>選択肢を追加</DialogTitle>
          <DialogDescription>{{ currentArea }} / {{ targetField?.field_name }}</DialogDescription>
        </DialogHeader>
        <div class="space-y-3 py-2">
          <div class="space-y-1.5">
            <Label>表示名 <span class="text-destructive">*</span></Label>
            <Input v-model="newOptionLabel" placeholder="例：右 橈骨動脈" @keydown.enter="submitAddOption" />
          </div>
        </div>
        <div class="flex justify-end gap-2">
          <Button variant="outline" @click="addOptionDialogOpen = false">キャンセル</Button>
          <Button @click="submitAddOption" :disabled="!newOptionLabel">追加</Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- カテゴリー（治療部位）追加 Dialog -->
    <Dialog v-model:open="addCategoryDialogOpen">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>カテゴリー（治療部位）を追加</DialogTitle>
          <DialogDescription>例：肩こり、頭痛</DialogDescription>
        </DialogHeader>
        <div class="space-y-3 py-2">
          <div class="space-y-1.5">
            <Label>カテゴリー名 <span class="text-destructive">*</span></Label>
            <Input v-model="newCategory.name" placeholder="例：肩こり" @keydown.enter="submitAddCategory" />
          </div>
          <div class="space-y-1.5">
            <Label>閲覧可能グレード（このグレード以上の先生が選択できます）</Label>
            <Select v-model="newCategory.required_tier">
              <SelectTrigger><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem :value="1">ベーシック</SelectItem>
                <SelectItem :value="2">アドバンス</SelectItem>
                <SelectItem :value="3">エキスパート</SelectItem>
                <SelectItem :value="4">マスター</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        <div class="flex justify-end gap-2">
          <Button variant="outline" @click="addCategoryDialogOpen = false">キャンセル</Button>
          <Button @click="submitAddCategory" :disabled="!newCategory.name">追加</Button>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { Plus, Eye, EyeOff, Trash2 } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input }  from '@/components/ui/input'
import { Label }  from '@/components/ui/label'
import { Badge }  from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

const props = defineProps({
  fields:         Array,
  treatmentAreas: Array,
  categories:     { type: Array, default: () => [] }, // 追加：{id, name, required_tier}
  fieldTypes:     Array,
  currentArea:    String,
})

const TIER_LABELS = { 1: 'ベーシック', 2: 'アドバンス', 3: 'エキスパート', 4: 'マスター' }

// タブの「(グレード以上)」表示用。ベーシック(1)は全員閲覧可なので表示しない
function categoryTierLabel(area) {
  const cat = props.categories.find(c => c.name === area)
  if (!cat || cat.required_tier <= 1) return null
  return TIER_LABELS[cat.required_tier] ?? null
}

const switchArea = (area) => {
  router.get(route('admin.form-fields.index'), { treatment_area: area }, {
    preserveState: true, replace: true,
  })
}

// ──────────────────────────────────────────
// 質問項目
// ──────────────────────────────────────────
const addFieldDialogOpen = ref(false)
const newField = reactive({ field_name: '', field_type: 'checkbox' })

const submitAddField = () => {
  router.post(route('admin.form-fields.store'), {
    treatment_area: props.currentArea,
    field_name:     newField.field_name,
    field_type:     newField.field_type,
  }, {
    preserveState: true,
    onSuccess: () => {
      addFieldDialogOpen.value = false
      newField.field_name = ''
      newField.field_type = 'checkbox'
    },
  })
}

const toggleField = (field) => {
  router.post(route('admin.form-fields.toggle', field.id), {}, { preserveState: true })
}

const destroyField = (field) => {
  if (!confirm(`「${field.field_name}」を削除しますか？選択肢も全て削除されます。`)) return
  router.delete(route('admin.form-fields.destroy', field.id), { preserveState: true })
}

// ──────────────────────────────────────────
// 選択肢
// ──────────────────────────────────────────
const addOptionDialogOpen = ref(false)
const targetField  = ref(null)
const newOptionLabel = ref('')

const openAddOption = (field) => {
  targetField.value      = field
  newOptionLabel.value   = ''
  addOptionDialogOpen.value = true
}

const submitAddOption = () => {
  if (!newOptionLabel.value) return
  router.post(route('admin.form-fields.store-option', targetField.value.id), {
    label: newOptionLabel.value,
  }, {
    preserveState: true,
    onSuccess: () => { addOptionDialogOpen.value = false },
  })
}

const toggleOption = (option) => {
  router.post(route('admin.form-fields.toggle-option', option.id), {}, { preserveState: true })
}

const destroyOption = (option) => {
  if (!confirm(`「${option.label}」を削除しますか？`)) return
  router.delete(route('admin.form-fields.destroy-option', option.id), { preserveState: true })
}

// ──────────────────────────────────────────
// カテゴリー（治療部位）追加
// ──────────────────────────────────────────
const addCategoryDialogOpen = ref(false)
const newCategory = reactive({ name: '', required_tier: 1 })

const submitAddCategory = () => {
  if (!newCategory.name) return
  router.post(route('admin.case-report-categories.store'), {
    name:          newCategory.name,
    required_tier: newCategory.required_tier,
  }, {
    preserveState: true,
    onSuccess: () => {
      addCategoryDialogOpen.value = false
      newCategory.name = ''
      newCategory.required_tier = 1
    },
  })
}
</script>
