<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from 'vue-i18n'
import RehabApplicationsTable from '@/Components/RehabApplicationsTable.vue'
import { usePage, Link } from '@inertiajs/vue3'
import { Video, ChevronRight, AlertTriangle, PlayCircle } from 'lucide-vue-next'
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

        <div class="px-6 pt-4 space-y-3">
            <!-- 未視聴の必須動画がある場合の警告 -->
            <Link
                v-if="unwatchedRequiredCount > 0"
                :href="route('reference-videos.index')"
                class="flex items-center justify-between p-4 rounded-lg border border-amber-300 bg-amber-50 hover:bg-amber-100 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <AlertTriangle class="w-4.5 h-4.5 text-amber-700" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-amber-900">未視聴の必須動画が{{ unwatchedRequiredCount }}本あります</p>
                        <p class="text-xs text-amber-700">動注治療の必須動画をご視聴ください</p>
                    </div>
                </div>
                <ChevronRight class="w-4 h-4 text-amber-700 shrink-0" />
            </Link>

            <!-- 参考動画への導線 -->
            <Link
                :href="route('reference-videos.index')"
                class="flex items-center justify-between p-4 rounded-lg border bg-white hover:bg-muted/50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <Video class="w-4.5 h-4.5 text-primary" />
                    </div>
                    <div>
                        <p class="text-sm font-medium">参考動画</p>
                        <p class="text-xs text-muted-foreground">動注治療の手技・注意事項の動画を見る</p>
                    </div>
                </div>
                <ChevronRight class="w-4 h-4 text-muted-foreground shrink-0" />
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