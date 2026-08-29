<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from 'vue-i18n'
import RehabApplicationsTable from '@/Components/RehabApplicationsTable.vue'
import { usePage, Link } from '@inertiajs/vue3'
import { Video, ChevronRight, AlertTriangle, PlayCircle, ClipboardList } from 'lucide-vue-next'
import axios from 'axios'

const { t } = useI18n()
const { props } = usePage()

const user = props.user
const applications = props.applications
const unwatchedRequiredCount = props.unwatchedRequiredVideosCount ?? 0
const notices = props.notices ?? []

function markViewed(notice) {
  if (notice.is_viewed) return
  notice.is_viewed = true
  axios.post(`/notices/${notice.id}/view`)
}
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>{{ user.name }}</template>

        <!-- 未視聴の必須動画がある場合の警告：上部の細いバナー -->
        <div v-if="unwatchedRequiredCount > 0" class="px-6 pt-4">
            <Link
                :href="route('reference-videos.index')"
                class="flex items-center gap-2 px-4 py-2.5 rounded-lg border border-amber-300 bg-amber-50 hover:bg-amber-100 transition-colors text-sm"
            >
                <AlertTriangle class="w-4 h-4 text-amber-700 shrink-0" />
                <span class="text-amber-900 font-medium">未視聴の必須動画が{{ unwatchedRequiredCount }}本あります</span>
                <span class="text-amber-700">動注治療の必須動画をご視聴ください</span>
                <ChevronRight class="w-4 h-4 text-amber-700 shrink-0 ml-auto" />
            </Link>
        </div>

        <!-- 症例報告・参考動画：横並びタイル -->
        <div class="px-6 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Link
                :href="route('reports.create')"
                class="flex flex-col gap-3 p-5 rounded-xl border border-primary/30 bg-primary/5 hover:bg-primary/10 transition-colors"
            >
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-lg bg-primary/15 flex items-center justify-center">
                        <ClipboardList class="w-5 h-5 text-primary" />
                    </div>
                    <ChevronRight class="w-4 h-4 text-primary" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-primary">症例報告を入力する</p>
                    <p class="text-xs text-primary/70 mt-0.5">動注ライセンス症例報告を新規登録する</p>
                </div>
            </Link>

            <Link
                :href="route('reference-videos.index')"
                class="flex flex-col gap-3 p-5 rounded-xl border bg-white hover:bg-muted/50 transition-colors"
            >
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-lg bg-muted flex items-center justify-center">
                        <Video class="w-5 h-5 text-muted-foreground" />
                    </div>
                    <ChevronRight class="w-4 h-4 text-muted-foreground" />
                </div>
                <div>
                    <p class="text-sm font-semibold">参考動画</p>
                    <p class="text-xs text-muted-foreground mt-0.5">動注治療の手技・注意事項の動画を見る</p>
                </div>
            </Link>
        </div>

        <!-- お知らせ -->
        <div class="px-6 mt-4">
            <div class="bg-white rounded-2xl shadow p-5">
                <h2 class="text-lg font-bold mb-4">お知らせ</h2>

                <p v-if="notices.length === 0" class="text-sm text-gray-400">
                    現在お知らせはありません。
                </p>

                <ul class="space-y-4">
                    <li
                        v-for="notice in notices"
                        :key="notice.id"
                        class="p-3 rounded-lg hover:bg-gray-50 transition border"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-gray-500">{{ notice.published_at }}</span>
                            <span v-if="!notice.is_viewed" class="text-xs text-red-500 font-semibold">未読</span>
                        </div>

                        <div class="font-semibold text-gray-800">{{ notice.title }}</div>

                        <div v-if="notice.body" class="text-sm text-gray-600 mt-1 whitespace-pre-line">
                            {{ notice.body }}
                        </div>

                        <div v-if="notice.embed_url" class="mt-3">
                            <div v-if="notice.is_video_available">
                                <iframe
                                    :src="notice.embed_url"
                                    class="w-full aspect-video rounded-lg"
                                    frameborder="0"
                                    allowfullscreen
                                    @load="markViewed(notice)"
                                ></iframe>
                            </div>
                            <div v-else class="text-sm text-gray-400 bg-gray-50 rounded-lg p-3 flex items-center gap-2">
                                <PlayCircle class="w-4 h-4" />
                                この動画の配信期間は終了しました
                                <span v-if="notice.video_available_until">({{ notice.video_available_until }}まで)</span>
                            </div>
                        </div>

                        <button
                            v-else-if="!notice.is_viewed"
                            @click="markViewed(notice)"
                            class="text-xs text-blue-600 mt-2"
                        >
                            既読にする
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>