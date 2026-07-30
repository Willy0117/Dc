<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MyPageNotice from './MyPageNotice.vue'
import { useI18n } from 'vue-i18n'
import RehabApplicationsTable from '@/Components/RehabApplicationsTable.vue'
import { usePage, Link } from '@inertiajs/vue3'
import { Video, ChevronRight, AlertTriangle } from 'lucide-vue-next'

const { t } = useI18n()
const { props } = usePage()

const user = props.user
const applications = props.applications
const unwatchedRequiredCount = props.unwatchedRequiredVideosCount ?? 0

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

        <MyPageNotice />
    </AppLayout>
</template>