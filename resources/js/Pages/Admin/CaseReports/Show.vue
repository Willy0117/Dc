<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">症例報告管理</p>
      <h1 class="text-xl font-semibold">症例報告詳細</h1>
    </template>

    <div class="p-6 max-w-3xl space-y-6">

      <!-- 戻るボタン -->
      <Button variant="outline" size="sm" as-child>
        <Link :href="route('admin.case-reports.index')">
          <ChevronLeft class="w-4 h-4 mr-1" />一覧に戻る
        </Link>
      </Button>

      <!-- 基本情報 -->
      <div class="border rounded-lg overflow-hidden">
        <div class="bg-muted px-4 py-2.5 border-b">
          <h2 class="text-sm font-semibold">基本情報</h2>
        </div>
        <div class="divide-y">
          <Row label="報告日" :value="report.submitted_at ? dayjs(report.submitted_at).format('YYYY/MM/DD HH:mm') : '-'" />
          <Row label="施設名" :value="report.organization?.name ?? report.facility_name_raw ?? '-'" />
          <Row label="先生" :value="report.member?.full_name ?? '-'" />
          <Row label="患者性別" :value="report.patient_gender ?? '-'" />
          <Row label="年代" :value="report.patient_age_group ?? '-'" />
          <Row label="治療部位" :value="report.treatment_area" />
        </div>
      </div>

      <!-- 部位別詳細 -->
      <div class="border rounded-lg overflow-hidden">
        <div class="bg-muted px-4 py-2.5 border-b">
          <h2 class="text-sm font-semibold">{{ report.treatment_area }}の詳細</h2>
        </div>
        <div class="divide-y">
          <!-- 手 -->
          <template v-if="report.treatment_area === '手' && report.hand_detail">
            <Row label="穿刺血管" :value="report.hand_detail.puncture_vessels?.join('、') ?? '-'" />
            <Row label="病名" :value="report.hand_detail.disease_names?.join('、') ?? '-'" />
            <Row label="右手疼痛部位" :value="report.hand_detail.right_pain_area_list?.join('、') ?? '-'" />
            <Row label="左手疼痛部位" :value="report.hand_detail.left_pain_area_list?.join('、') ?? '-'" />
          </template>

          <!-- 足・肘・肩・膝 -->
          <template v-else-if="detail">
            <Row label="穿刺血管" :value="detail.puncture_vessels?.join('、') ?? '-'" />
            <Row label="病名" :value="detail.disease_names?.join('、') ?? '-'" />
            <Row label="駆血部位" :value="detail.tourniquet_position ?? '-'" />
          </template>
        </div>
      </div>

      <!-- トラブル・合併症 -->
      <div class="border rounded-lg overflow-hidden">
        <div class="bg-muted px-4 py-2.5 border-b">
          <h2 class="text-sm font-semibold">備考・コメント</h2>
        </div>
        <div class="divide-y">
          <div class="px-4 py-3">
            <p class="text-xs text-muted-foreground mb-1">トラブル・合併症</p>
            <div v-if="report.complication_types?.length" class="flex flex-wrap gap-1.5">
              <Badge v-for="c in report.complication_types" :key="c" variant="secondary">{{ c }}</Badge>
            </div>
            <p v-else class="text-sm text-muted-foreground">なし</p>
          </div>
          <Row label="特記事項" :value="report.notes ?? '-'" />
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { ChevronLeft } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge }  from '@/components/ui/badge'

// シンプルな行コンポーネント
const Row = {
  props: ['label', 'value'],
  template: `
    <div class="px-4 py-3 flex gap-4">
      <span class="text-xs text-muted-foreground w-32 shrink-0">{{ label }}</span>
      <span class="text-sm">{{ value }}</span>
    </div>
  `,
}

const props = defineProps({
  report: Object,
})

const detail = computed(() => {
  const area = props.report.treatment_area
  if (area === '足') return props.report.foot_detail
  if (area === '肘') return props.report.elbow_detail
  if (area === '肩') return props.report.shoulder_detail
  if (area === '膝') return props.report.knee_detail
  return null
})
</script>