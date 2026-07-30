<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Switch } from '@/components/ui/switch'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

// videoSet が null の場合は新規作成モード、値がある場合は編集モード（同じ画面で両方扱う）
const props = defineProps({
  videoSet: {
    type: Object,
    default: null,
  },
})

const isEditMode = computed(() => props.videoSet !== null)

// ── 基本情報フォーム ─────────────────────
const infoForm = useForm({
  name: props.videoSet?.name ?? '',
  category: props.videoSet?.category ?? '',
  theme: props.videoSet?.theme ?? '',
  description: props.videoSet?.description ?? '',
  price_jpy: props.videoSet?.price_jpy ?? 9900,
  stripe_price_id: props.videoSet?.stripe_price_id ?? '',
  passing_score: props.videoSet?.passing_score ?? 100,
  active: props.videoSet?.active ?? true,
})

function saveInfo() {
  if (isEditMode.value) {
    infoForm.put(route('admin.video-sets.update', props.videoSet.id))
  } else {
    infoForm.post(route('admin.video-sets.store'))
  }
}

// ── 動画追加フォーム（編集モードのみ使用） ─────────────────────
const videoForm = useForm({
  title: '',
  vimeo_id: '',
  vimeo_hash: '',
})

function addVideo() {
  videoForm.post(route('admin.video-sets.videos.store', props.videoSet.id), {
    preserveScroll: true,
    onSuccess: () => videoForm.reset(),
  })
}

function deleteVideo(video) {
  if (!confirm(`「${video.title}」を削除しますか？`)) return
  router.delete(route('admin.video-sets.videos.destroy', [props.videoSet.id, video.id]), {
    preserveScroll: true,
  })
}

// ── 設問管理（動画=講義単位） ─────────────────────
// どの動画の「設問追加フォーム」を開いているか（動画IDをキーにしたフォームの入れ物）
function emptyQuestionForm() {
  return {
    question_text: '',
    choices: [
      { choice_text: '', is_correct: true },
      { choice_text: '', is_correct: false },
    ],
  }
}

const questionForms = reactive({}) // { [videoId]: questionFormState }
const openQuestionFormVideoId = reactive({ value: null })

function toggleQuestionForm(videoId) {
  if (openQuestionFormVideoId.value === videoId) {
    openQuestionFormVideoId.value = null
    return
  }
  if (!questionForms[videoId]) {
    questionForms[videoId] = emptyQuestionForm()
  }
  openQuestionFormVideoId.value = videoId
}

function addChoice(videoId) {
  questionForms[videoId].choices.push({ choice_text: '', is_correct: false })
}

function removeChoice(videoId, index) {
  questionForms[videoId].choices.splice(index, 1)
}

function setCorrect(videoId, index) {
  questionForms[videoId].choices.forEach((c, i) => (c.is_correct = i === index))
}

function addQuestion(video) {
  router.post(
    route('admin.video-sets.videos.questions.store', [props.videoSet.id, video.id]),
    questionForms[video.id],
    {
      preserveScroll: true,
      onSuccess: () => {
        questionForms[video.id] = emptyQuestionForm()
      },
    }
  )
}

function deleteQuestion(video, question) {
  if (!confirm('この設問を削除しますか？')) return
  router.delete(
    route('admin.video-sets.videos.questions.destroy', [props.videoSet.id, video.id, question.id]),
    { preserveScroll: true }
  )
}
</script>

<template>
  <Head :title="isEditMode ? `動画セット編集: ${videoSet.name}` : '動画セット新規作成'" />
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">動画講習管理</p>
      <h1 class="text-xl font-semibold">
        {{ isEditMode ? `セット${videoSet.name}：${videoSet.category}` : '動画セット新規作成' }}
      </h1>
    </template>

    <div class="p-6 max-w-3xl space-y-8">

      <!-- 基本情報 -->
      <Card>
        <CardHeader><CardTitle>基本情報</CardTitle></CardHeader>
        <CardContent class="space-y-4">
          <div>
            <Label>セット名（A/B/C/D等）</Label>
            <Input v-model="infoForm.name" />
            <p v-if="infoForm.errors.name" class="text-sm text-destructive">{{ infoForm.errors.name }}</p>
          </div>
          <div>
            <Label>カテゴリ（例：医療安全の基本的知識）</Label>
            <Input v-model="infoForm.category" />
          </div>
          <div>
            <Label>テーマ（例：基礎知識・ヒューマンエラー・質改善）</Label>
            <Input v-model="infoForm.theme" />
          </div>
          <div>
            <Label>説明</Label>
            <Textarea v-model="infoForm.description" rows="4" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label>価格（円）</Label>
              <Input type="number" v-model.number="infoForm.price_jpy" />
            </div>
            <div>
              <Label>合格ライン（%）</Label>
              <Input type="number" v-model.number="infoForm.passing_score" />
            </div>
          </div>
          <div>
            <Label>Stripe Price ID</Label>
            <Input v-model="infoForm.stripe_price_id" placeholder="price_xxxxxxxxxxxx" />
          </div>
          <div class="flex items-center gap-2">
            <Switch v-model:checked="infoForm.active" />
            <Label>公開する</Label>
          </div>
          <Button @click="saveInfo" :disabled="infoForm.processing">
            {{ isEditMode ? '保存する' : '作成する' }}
          </Button>
        </CardContent>
      </Card>

      <!-- 動画・設問管理（編集モードのみ。新規作成後にこの画面に遷移してから追加する） -->
      <template v-if="isEditMode">
        <Card>
          <CardHeader><CardTitle>動画（{{ videoSet.videos.length }}本）</CardTitle></CardHeader>
          <CardContent class="space-y-4">
            <div v-for="video in videoSet.videos" :key="video.id" class="border rounded p-3 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium">{{ video.title }}</p>
                  <p class="text-xs text-muted-foreground">Vimeo ID: {{ video.vimeo_id }} / hash: {{ video.vimeo_hash }}</p>
                </div>
                <Button variant="destructive" size="sm" @click="deleteVideo(video)">動画を削除</Button>
              </div>

              <!-- この動画の設問一覧 -->
              <div class="pl-3 border-l-2 space-y-2">
                <p class="text-xs font-semibold text-muted-foreground">
                  確認テスト設問（{{ video.questions?.length ?? 0 }}問）
                </p>
                <div v-for="q in video.questions" :key="q.id" class="bg-muted/40 rounded p-2 space-y-1">
                  <div class="flex items-start justify-between">
                    <p class="text-sm font-medium">{{ q.question_text }}</p>
                    <Button variant="ghost" size="sm" @click="deleteQuestion(video, q)">✕</Button>
                  </div>
                  <ul class="text-xs space-y-0.5">
                    <li v-for="c in q.choices" :key="c.id" :class="c.is_correct ? 'text-green-600 font-medium' : ''">
                      {{ c.is_correct ? '✔ ' : '・' }}{{ c.choice_text }}
                    </li>
                  </ul>
                </div>

                <Button variant="outline" size="sm" @click="toggleQuestionForm(video.id)">
                  {{ openQuestionFormVideoId.value === video.id ? '閉じる' : '設問を追加' }}
                </Button>

                <div v-if="openQuestionFormVideoId.value === video.id" class="space-y-2 pt-2">
                  <Textarea v-model="questionForms[video.id].question_text" placeholder="設問文" rows="2" />
                  <div v-for="(choice, i) in questionForms[video.id].choices" :key="i" class="flex items-center gap-2">
                    <input type="radio" :name="`correct-${video.id}`" :checked="choice.is_correct" @change="setCorrect(video.id, i)" />
                    <Input v-model="choice.choice_text" :placeholder="`選択肢${i + 1}`" class="flex-1" />
                    <Button variant="ghost" size="sm" @click="removeChoice(video.id, i)" v-if="questionForms[video.id].choices.length > 2">✕</Button>
                  </div>
                  <Button variant="outline" size="sm" @click="addChoice(video.id)">選択肢を追加</Button>
                  <div>
                    <Button size="sm" @click="addQuestion(video)">この設問を保存</Button>
                  </div>
                </div>
              </div>
            </div>

            <div class="border-t pt-4 space-y-3">
              <p class="font-medium text-sm">動画を追加</p>
              <Input v-model="videoForm.title" placeholder="タイトル（例: 総論（２）医療安全の歴史から理解する基本知識（１・２））" />
              <div class="grid grid-cols-2 gap-3">
                <Input v-model="videoForm.vimeo_id" placeholder="Vimeo ID" />
                <Input v-model="videoForm.vimeo_hash" placeholder="限定公開hash（任意）" />
              </div>
              <Button @click="addVideo" :disabled="videoForm.processing">動画を追加</Button>
            </div>
          </CardContent>
        </Card>
      </template>

      <p v-else class="text-sm text-muted-foreground">
        作成後、この画面に切り替わり、動画・確認テストの設問を追加できます。
      </p>
    </div>
  </AppLayout>
</template>
