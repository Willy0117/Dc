<script setup>
import { Head, Link } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { ArrowLeft, CheckCircle2, XCircle, Award, Download } from 'lucide-vue-next'

import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const props = defineProps({
  order: Object,
  videos: Array,
})

const statusLabel = {
  paid: '購入済み',
  pending: '保留',
  refunded: '返金済み',
}
</script>

<template>
  <Head :title="`注文詳細: ${order.customer_name}`" />
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">動画講習管理</p>
      <h1 class="text-xl font-semibold">注文詳細</h1>
    </template>

    <div class="p-6 max-w-3xl space-y-6">
      <Link :href="route('admin.orders.index')" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:underline">
        <ArrowLeft class="w-4 h-4" />注文一覧に戻る
      </Link>

      <!-- 購入者・決済情報 -->
      <Card>
        <CardHeader><CardTitle>購入者情報</CardTitle></CardHeader>
        <CardContent class="space-y-2 text-sm">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <p class="text-xs text-muted-foreground">氏名</p>
              <p class="font-medium">{{ order.customer_name }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">所属</p>
              <p class="font-medium">{{ order.affiliation ?? '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">TEL</p>
              <p class="font-medium">{{ order.phone ?? '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">メールアドレス</p>
              <p class="font-medium">{{ order.customer_email }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">購入セット</p>
              <p class="font-medium">セット{{ order.video_set?.name }}：{{ order.video_set?.category }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">状態</p>
              <Badge :variant="order.status === 'paid' ? 'default' : 'secondary'">
                {{ statusLabel[order.status] ?? order.status }}
              </Badge>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">決済日時</p>
              <p class="font-medium">{{ order.paid_at ? dayjs(order.paid_at).format('YYYY/MM/DD HH:mm') : '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">Stripeセッション</p>
              <p class="font-mono text-xs">{{ order.stripe_checkout_session_id }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- 動画ごとの視聴・テスト・証明書状況 -->
      <Card>
        <CardHeader><CardTitle>受講状況（動画ごと）</CardTitle></CardHeader>
        <CardContent class="space-y-3">
          <div v-for="video in videos" :key="video.id" class="border rounded p-3 space-y-2">
            <p class="font-medium text-sm">{{ video.title }}</p>

            <div class="flex items-center gap-2 text-sm">
              <CheckCircle2 v-if="video.watched" class="w-4 h-4 text-emerald-600" />
              <XCircle v-else class="w-4 h-4 text-muted-foreground" />
              <span>
                視聴{{ video.watched ? '完了' : '未完了' }}
                <span v-if="video.watched_at" class="text-xs text-muted-foreground">
                  （{{ dayjs(video.watched_at).format('YYYY/MM/DD HH:mm') }}）
                </span>
              </span>
            </div>

            <div v-if="video.quiz_attempts.length > 0" class="text-sm">
              <p class="text-xs text-muted-foreground mb-1">テスト受験履歴</p>
              <ul class="space-y-0.5">
                <li v-for="(a, i) in video.quiz_attempts" :key="i" class="flex items-center gap-2">
                  <Badge :variant="a.passed ? 'default' : 'secondary'" class="text-xs">
                    {{ a.passed ? '合格' : '不合格' }}
                  </Badge>
                  <span>{{ a.score_percent }}%</span>
                  <span class="text-xs text-muted-foreground">{{ dayjs(a.created_at).format('YYYY/MM/DD HH:mm') }}</span>
                </li>
              </ul>
            </div>

            <div v-if="video.certificate" class="flex items-center gap-2 text-sm">
              <Award class="w-4 h-4 text-amber-600" />
              <span class="font-mono text-xs">{{ video.certificate.certificate_number }}</span>
              <Button variant="outline" size="sm" as-child>
                <a :href="route('admin.certificates.download', video.certificate.id)">
                  <Download class="w-3 h-3 mr-1" />PDF
                </a>
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
