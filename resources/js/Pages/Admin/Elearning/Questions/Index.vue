<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">e-ラーニング管理</p>
      <h1 class="text-xl font-semibold">問題一覧（全{{ questions.length }}問）</h1>
    </template>

    <div class="p-6 space-y-4">

      <div class="flex justify-end">
        <Button type="button" size="sm" @click="openCreateDialog">
          <Plus class="w-3.5 h-3.5 mr-1" />問題を追加
        </Button>
      </div>

      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b">
            <tr>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground w-16">出題</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">設問</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground w-20">正答</th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold text-muted-foreground w-24">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="questions.length === 0">
              <td colspan="4" class="px-3 py-12 text-center text-muted-foreground">問題がありません</td>
            </tr>
            <tr
              v-for="q in questions"
              :key="q.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors"
            >
              <td class="px-3 py-2.5">
                <button type="button" @click="toggleActive(q)">
                  <Badge v-if="q.is_active" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-100 cursor-pointer">ON</Badge>
                  <Badge v-else variant="outline" class="text-muted-foreground cursor-pointer">OFF</Badge>
                </button>
              </td>
              <td class="px-3 py-2.5 max-w-xl">
                <p class="truncate">{{ q.question }}</p>
              </td>
              <td class="px-3 py-2.5 font-mono text-xs">{{ q.correct_answer }}</td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" class="h-7 w-7" @click="openEditDialog(q)">
                    <Pencil class="w-3.5 h-3.5" />
                  </Button>
                  <Button variant="ghost" size="icon" class="h-7 w-7 text-destructive" @click="deleteQuestion(q)">
                    <Trash2 class="w-3.5 h-3.5" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 追加・編集ダイアログ -->
    <Teleport to="body">
      <div v-if="dialogOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="dialogOpen = false">
        <div class="w-full max-w-2xl bg-white rounded-lg shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-5 py-4 border-b shrink-0">
            <h2 class="font-bold text-sm">{{ editingId ? '問題を編集' : '問題を追加' }}</h2>
            <Button variant="ghost" size="icon" class="h-7 w-7" @click="dialogOpen = false">
              <X class="w-4 h-4" />
            </Button>
          </div>

          <div class="p-5 space-y-4 overflow-y-auto">
            <div class="space-y-1.5">
              <Label class="text-xs text-muted-foreground">設問</Label>
              <Textarea v-model="form.question" rows="2" />
              <p v-if="form.errors.question" class="text-xs text-destructive">{{ form.errors.question }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div v-for="letter in ['a', 'b', 'c', 'd']" :key="letter" class="space-y-1.5">
                <Label class="text-xs text-muted-foreground">選択肢{{ letter.toUpperCase() }}</Label>
                <Input v-model="form[`choice_${letter}`]" />
                <p v-if="form.errors[`choice_${letter}`]" class="text-xs text-destructive">{{ form.errors[`choice_${letter}`] }}</p>
              </div>
            </div>

            <div class="space-y-1.5">
              <Label class="text-xs text-muted-foreground">正答</Label>
              <div class="flex gap-2">
                <button
                  v-for="letter in ['A', 'B', 'C', 'D']"
                  :key="letter"
                  type="button"
                  class="w-10 h-10 rounded-lg border text-sm font-semibold transition-colors"
                  :class="form.correct_answer === letter ? 'bg-primary text-primary-foreground border-primary' : 'bg-background border-border hover:bg-muted'"
                  @click="form.correct_answer = letter"
                >
                  {{ letter }}
                </button>
              </div>
              <p v-if="form.errors.correct_answer" class="text-xs text-destructive">{{ form.errors.correct_answer }}</p>
            </div>

            <div class="space-y-1.5">
              <Label class="text-xs text-muted-foreground">解説</Label>
              <Textarea v-model="form.explanation" rows="3" />
            </div>

            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="checkbox" v-model="form.is_active" class="rounded" />
              出題対象にする
            </label>
          </div>

          <div class="px-5 py-4 border-t flex justify-end gap-2 shrink-0">
            <Button type="button" variant="outline" @click="dialogOpen = false">キャンセル</Button>
            <Button type="button" :disabled="form.processing" @click="save">保存</Button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Plus, Pencil, Trash2, X } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'

defineProps({
  questions: { type: Array, default: () => [] },
})

const dialogOpen = ref(false)
const editingId = ref(null)

const form = useForm({
  question: '',
  choice_a: '',
  choice_b: '',
  choice_c: '',
  choice_d: '',
  correct_answer: 'A',
  explanation: '',
  is_active: true,
})

function openCreateDialog() {
  editingId.value = null
  form.reset()
  form.correct_answer = 'A'
  form.is_active = true
  dialogOpen.value = true
}

function openEditDialog(q) {
  editingId.value = q.id
  form.question = q.question
  form.choice_a = q.choice_a
  form.choice_b = q.choice_b
  form.choice_c = q.choice_c
  form.choice_d = q.choice_d
  form.correct_answer = q.correct_answer
  form.explanation = q.explanation
  form.is_active = q.is_active
  dialogOpen.value = true
}

function save() {
  const options = {
    preserveScroll: true,
    onSuccess: () => { dialogOpen.value = false },
  }
  if (editingId.value) {
    form.put(route('admin.elearning-questions.update', editingId.value), options)
  } else {
    form.post(route('admin.elearning-questions.store'), options)
  }
}

function deleteQuestion(q) {
  if (!confirm('この問題を削除しますか？')) return
  router.delete(route('admin.elearning-questions.destroy', q.id), { preserveScroll: true })
}

function toggleActive(q) {
  router.post(route('admin.elearning-questions.toggle-active', q.id), {}, { preserveScroll: true })
}
</script>
