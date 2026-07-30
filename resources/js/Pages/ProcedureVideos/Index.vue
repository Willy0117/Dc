<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">手技動画</p>
      <h1 class="text-xl font-semibold">手技動画アップロード</h1>
    </template>

    <div class="p-6 space-y-6">

      <!-- アップロードエリア -->
      <div class="border rounded-lg p-6 space-y-4 bg-white">
        <h2 class="text-sm font-semibold text-muted-foreground">動画をアップロード</h2>

        <div
          class="relative flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-10 text-center transition-colors"
          :class="isDragging ? 'border-primary bg-primary/5' : 'border-muted-foreground/25 bg-muted/20'"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop"
        >
          <UploadCloud class="w-8 h-8 text-muted-foreground" />
          <p class="text-sm text-muted-foreground">
            ここに動画ファイルをドラッグ＆ドロップ
            <br />または
          </p>
          <Button type="button" variant="outline" size="sm" @click="fileInput?.click()">
            ファイルを選択
          </Button>
          <input
            ref="fileInput"
            type="file"
            accept="video/mp4,video/quicktime,video/x-m4v"
            class="hidden"
            @change="handleFileSelect"
          />
          <p class="text-xs text-muted-foreground/70">MP4 / MOV（上限500MB）</p>
        </div>

        <!-- 選択中ファイル＋サムネイルプレビュー -->
        <div v-if="selectedFile" class="flex items-center justify-between gap-3 rounded-lg border bg-muted/20 px-4 py-3">
          <div class="flex items-center gap-3 min-w-0">
            <img
              v-if="thumbnailPreviewUrl"
              :src="thumbnailPreviewUrl"
              class="w-14 h-9 object-cover rounded shrink-0 border"
            />
            <Video v-else class="w-5 h-5 text-muted-foreground shrink-0" />
            <div class="min-w-0">
              <p class="text-sm font-medium truncate">{{ selectedFile.name }}</p>
              <p class="text-xs text-muted-foreground">{{ formatFileSize(selectedFile.size) }}</p>
            </div>
          </div>
          <Button type="button" variant="ghost" size="icon" class="h-7 w-7 shrink-0" @click="clearFile" :disabled="uploading">
            <X class="w-3.5 h-3.5" />
          </Button>
        </div>

        <!-- タイトル・先生選択 -->
        <div v-if="selectedFile" class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">タイトル（任意）</Label>
            <Input v-model="title" placeholder="例：大腿骨動注治療 手技デモ" :disabled="uploading" />
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">先生（任意）</Label>
            <Select v-model="memberId" :disabled="uploading">
              <SelectTrigger><SelectValue placeholder="選択しない" /></SelectTrigger>
              <SelectContent>
                <SelectItem v-for="m in members" :key="m.id" :value="m.id">
                  {{ m.last_name }} {{ m.first_name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- 進捗バー -->
        <div v-if="uploading" class="space-y-1.5">
          <div class="h-2 rounded-full bg-muted overflow-hidden">
            <div class="h-full bg-primary transition-all" :style="{ width: `${uploadProgress}%` }" />
          </div>
          <p class="text-xs text-muted-foreground text-right">{{ uploadProgress }}%</p>
        </div>

        <p v-if="errorMessage" class="text-xs text-destructive">{{ errorMessage }}</p>

        <div class="flex justify-end">
          <Button type="button" :disabled="!selectedFile || uploading" @click="upload">
            <UploadCloud class="w-3.5 h-3.5 mr-1" />
            {{ uploading ? 'アップロード中...' : 'アップロード' }}
          </Button>
        </div>
      </div>

      <!-- 一覧 -->
      <div class="space-y-3">
        <h2 class="text-sm font-semibold text-muted-foreground">アップロード済み動画（全{{ videos.length }}件）</h2>

        <div class="border rounded-lg overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-muted border-b">
              <tr>
                <th class="px-4 py-2.5 w-20 text-left text-xs font-semibold text-muted-foreground">サムネイル</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">タイトル</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">先生</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">サイズ</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">アップロード日時</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="videos.length === 0">
                <td colspan="5" class="px-4 py-12 text-center text-muted-foreground">
                  アップロード済みの動画はありません
                </td>
              </tr>
              <tr
                v-for="video in videos"
                :key="video.id"
                class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors"
              >
                <td class="px-4 py-2.5">
                  <a :href="video.file_url" target="_blank">
                    <img
                      v-if="video.thumbnail_url"
                      :src="video.thumbnail_url"
                      class="w-14 h-9 object-cover rounded border"
                    />
                    <span v-else class="flex items-center justify-center w-14 h-9 rounded bg-muted text-muted-foreground">
                      <Video class="w-4 h-4" />
                    </span>
                  </a>
                </td>
                <td class="px-4 py-2.5">
                  <a :href="video.file_url" target="_blank" class="font-medium hover:underline">
                    {{ video.title || '(タイトルなし)' }}
                  </a>
                </td>
                <td class="px-4 py-2.5 text-sm text-muted-foreground">{{ video.member_name ?? '-' }}</td>
                <td class="px-4 py-2.5 text-sm text-muted-foreground">{{ formatFileSize(video.file_size) }}</td>
                <td class="px-4 py-2.5 text-sm text-muted-foreground">{{ video.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { UploadCloud, Video, X } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  videos:  { type: Array, default: () => [] },
  members: { type: Array, default: () => [] },
})

const fileInput = ref(null)
const selectedFile = ref(null)
const title = ref('')
const memberId = ref(null)
const isDragging = ref(false)
const uploading = ref(false)
const uploadProgress = ref(0)
const errorMessage = ref('')
const thumbnailBlob = ref(null)
const thumbnailPreviewUrl = ref(null)

const ACCEPTED_TYPES = ['video/mp4', 'video/quicktime', 'video/x-m4v']
const MAX_FILE_SIZE = 500 * 1024 * 1024 // 500MB

// ──────────────────────────────────────────
// サムネイル生成（ブラウザ側、canvas使用）
// ──────────────────────────────────────────
function generateThumbnail(file) {
  return new Promise((resolve) => {
    const video = document.createElement('video')
    video.preload = 'metadata'
    video.muted = true
    video.src = URL.createObjectURL(file)

    video.onloadeddata = () => {
      video.currentTime = Math.min(1, video.duration / 2)
    }
    video.onseeked = () => {
      const canvas = document.createElement('canvas')
      const scale = 320 / video.videoWidth
      canvas.width = 320
      canvas.height = video.videoHeight * scale
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height)
      canvas.toBlob((blob) => {
        URL.revokeObjectURL(video.src)
        resolve(blob)
      }, 'image/jpeg', 0.8)
    }
    video.onerror = () => resolve(null)
  })
}

async function validateAndSetFile(file) {
  errorMessage.value = ''
  if (!file) return
  if (!ACCEPTED_TYPES.includes(file.type)) {
    errorMessage.value = 'MP4またはMOV形式のファイルを選択してください。'
    return
  }
  if (file.size > MAX_FILE_SIZE) {
    errorMessage.value = `ファイルサイズが上限（500MB）を超えています。（選択されたファイル: ${formatFileSize(file.size)}）`
    return
  }
  selectedFile.value = file

  // サムネイル生成（失敗してもアップロード自体は継続できるようにする）
  const blob = await generateThumbnail(file)
  if (blob) {
    thumbnailBlob.value = blob
    thumbnailPreviewUrl.value = URL.createObjectURL(blob)
  }
}

function handleFileSelect(e) {
  validateAndSetFile(e.target.files?.[0])
}

function handleDrop(e) {
  isDragging.value = false
  validateAndSetFile(e.dataTransfer?.files?.[0])
}

function clearFile() {
  selectedFile.value = null
  title.value = ''
  memberId.value = null
  thumbnailBlob.value = null
  if (thumbnailPreviewUrl.value) URL.revokeObjectURL(thumbnailPreviewUrl.value)
  thumbnailPreviewUrl.value = null
  if (fileInput.value) fileInput.value.value = ''
}

function formatFileSize(bytes) {
  if (!bytes) return '-'
  const mb = bytes / (1024 * 1024)
  return mb >= 1024 ? `${(mb / 1024).toFixed(2)} GB` : `${mb.toFixed(1)} MB`
}

async function upload() {
  if (!selectedFile.value) return
  errorMessage.value = ''
  uploading.value = true
  uploadProgress.value = 0

  try {
    // Step1: 動画のpresigned URL取得
    const { data: presign } = await axios.post(route('procedure-videos.presign'), {
      filename: selectedFile.value.name,
      file_size: selectedFile.value.size,
      content_type: selectedFile.value.type,
      kind: 'video',
    })

    // Step2: S3へ直接PUT（動画本体・進捗は90%まで割り当て）
    await axios.put(presign.upload_url, selectedFile.value, {
      headers: {
        'Content-Type': selectedFile.value.type,
        ...presign.headers,
      },
      onUploadProgress: (e) => {
        uploadProgress.value = Math.round((e.loaded / e.total) * 90)
      },
    })

    // Step3: サムネイルアップロード（失敗しても動画登録は続行）
    let thumbnailKey = null
    if (thumbnailBlob.value) {
      try {
        const { data: thumbPresign } = await axios.post(route('procedure-videos.presign'), {
          filename: 'thumbnail.jpg',
          file_size: thumbnailBlob.value.size,
          content_type: 'image/jpeg',
          kind: 'thumbnail',
        })
        await axios.put(thumbPresign.upload_url, thumbnailBlob.value, {
          headers: { 'Content-Type': 'image/jpeg', ...thumbPresign.headers },
        })
        thumbnailKey = thumbPresign.key
      } catch (thumbError) {
        console.warn('サムネイルのアップロードに失敗しました', thumbError)
      }
    }
    uploadProgress.value = 95

    // Step4: 完了通知・DB登録
    await axios.post(route('procedure-videos.store'), {
      title: title.value,
      member_id: memberId.value,
      key: presign.key,
      thumbnail_key: thumbnailKey,
      file_size: selectedFile.value.size,
    })

    uploadProgress.value = 100
    router.reload({ only: ['videos'] })
    clearFile()
  } catch (e) {
    errorMessage.value = e.response?.data?.message ?? 'アップロードに失敗しました。'
  } finally {
    uploading.value = false
    uploadProgress.value = 0
  }
}
</script>