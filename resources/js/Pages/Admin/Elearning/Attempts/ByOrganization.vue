<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">e-ラーニング管理</p>
      <h1 class="text-xl font-semibold">{{ organization.name }} の受験状況</h1>
    </template>

    <div class="p-6 space-y-4">
      <p class="text-sm text-muted-foreground">対象期間：{{ periodLabel }}</p>

      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b-2 border-border">
            <tr>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">先生</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">合否</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">受験回数</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">直近の結果</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">合格日時</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="members.length === 0">
              <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">対象の先生アカウントがありません</td>
            </tr>
            <tr
              v-for="m in members"
              :key="m.member_id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors"
            >
              <td class="px-3 py-2.5 text-sm font-medium">{{ m.member_name }}</td>
              <td class="px-3 py-2.5">
                <Badge v-if="m.is_passed" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-100">合格</Badge>
                <Badge v-else-if="m.attempt_count > 0" variant="destructive" class="opacity-80">不合格</Badge>
                <Badge v-else variant="outline" class="text-muted-foreground">未受験</Badge>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">{{ m.attempt_count }}回</td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">
                <span v-if="m.latest_result">
                  {{ m.latest_result.correct_count }} / {{ m.latest_result.total_questions }}（{{ m.latest_result.submitted_at }}）
                </span>
                <span v-else>-</span>
              </td>
              <td class="px-3 py-2.5 text-sm text-muted-foreground">{{ m.passed_at ?? '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Badge } from '@/components/ui/badge'

defineProps({
  organization: Object,
  periodLabel:  String,
  members:      { type: Array, default: () => [] },
})
</script>
