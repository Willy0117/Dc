<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">参考動画</p>
      <h1 class="text-xl font-semibold">動注治療 参考動画</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- 未視聴の必須動画がある場合の注意バナー -->
      <div v-if="unwatchedRequiredCount > 0" class="flex items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
        <AlertTriangle class="w-4 h-4 shrink-0" />
        未視聴の必須動画が{{ unwatchedRequiredCount }}本あります。すべてご視聴ください。
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

      <!-- 動画グリッド -->
      <div v-if="currentVideos.length === 0" class="py-16 text-center text-muted-foreground">
        <Video class="w-8 h-8 mx-auto mb-2 opacity-30" />
        このカテゴリの動画はまだありません
      </div>
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="video in currentVideos"
          :key="video.id"
          class="border rounded-lg overflow-hidden bg-white cursor-pointer hover:shadow-md transition-shadow relative"
          @click="openVideo(video)"
        >
          <!-- 必須・視聴済みバッジ -->
          <div class="absolute top-2 left-2 z-10 flex gap-1">
            <span v-if="video.is_required" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-destructive text-destructive-foreground">
              必須
            </span>
            <span v-if="video.is_watched" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-600 text-white flex items-center gap-0.5">
              <Check class="w-2.5 h-2.5" />視聴済み
            </span>
          </div>

          <div class="relative aspect-video bg-muted">
            <img
              v-if="video.thumbnail_url"
              :src="video.thumbnail_url"
              class="w-full h-full object-cover"
            />
            <div class="absolute inset-0 flex items-center justify-center bg-black/20">
              <div class="w-10 h-10 rounded-full bg-white/90 flex items-center justify-center">
                <Play class="w-5 h-5 text-primary ml-0.5" />
              </div>
            </div>
          </div>
          <div class="p-3">
            <p class="text-sm font-medium line-clamp-2">{{ video.title }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 再生モーダル -->
    <Teleport to="body">
      <div v-if="playingVideo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="closeVideo">
        <div class="w-full max-w-3xl bg-black rounded-lg overflow-hidden">
          <div class="flex items-center justify-between px-4 py-2 bg-background">
            <p class="text-sm font-medium truncate pr-2">{{ playingVideo.title }}</p>
            <button type="button" class="text-muted-foreground hover:text-foreground shrink-0" @click="closeVideo">
              <X class="w-4 h-4" />
            </button>
          </div>
          <div class="aspect-video">
            <iframe
              :src="playingVideo.embed_url"
              class="w-full h-full"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
            />
          </div>
          <div v-if="playingVideo.is_required" class="px-4 py-3 bg-background flex items-center justify-between gap-3">
            <p class="text-xs text-muted-foreground">視聴が終わったらボタンを押してください</p>
            <Button
              type="button"
              size="sm"
              :disabled="playingVideo.is_watched || markingWatched"
              @click="markWatched(playingVideo)"
            >
              <Check class="w-3.5 h-3.5 mr-1" />
              {{ playingVideo.is_watched ? '視聴済み' : '視聴済みにする' }}
            </Button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { Video, Play, X, Check, AlertTriangle } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'

const props = defineProps({
  videosByCategory: { type: Object, default: () => ({}) },
  categories:       { type: Array, default: () => [] },
})

const activeCategory = ref(props.categories[0] ?? '全体')
const playingVideo    = ref(null)
const markingWatched  = ref(false)

const currentVideos = computed(() => props.videosByCategory[activeCategory.value] ?? [])

const unwatchedRequiredCount = computed(() => {
  let count = 0
  for (const cat of props.categories) {
    count += (props.videosByCategory[cat] ?? []).filter(v => v.is_required && !v.is_watched).length
  }
  return count
})

function openVideo(video) {
  playingVideo.value = video
}

function closeVideo() {
  playingVideo.value = null
}

async function markWatched(video) {
  markingWatched.value = true
  try {
    await axios.post(route('reference-videos.mark-watched', video.id))
    video.is_watched = true
    router.reload({ only: ['videosByCategory'] })
  } catch (e) {
    alert('視聴済みの記録に失敗しました。')
  } finally {
    markingWatched.value = false
  }
}
</script>
