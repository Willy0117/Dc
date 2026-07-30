<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">e-ラーニング</p>
      <h1 class="text-xl font-semibold">受験結果一覧</h1>
    </template>

    <div class="p-6 max-w-3xl space-y-4">

      <!-- 対象外の場合 -->
      <div v-if="!isEligible" class="border rounded-lg p-8 bg-white text-center space-y-2">
        <Info class="w-8 h-8 mx-auto text-muted-foreground opacity-40" />
        <p class="text-sm font-medium text-muted-foreground">受験結果はありません</p>
      </div>

      <template v-else>
        <div v-if="attempts.length === 0" class="border rounded-lg p-8 bg-white text-center space-y-2">
          <ClipboardList class="w-8 h-8 mx-auto text-muted-foreground opacity-40" />
          <p class="text-sm font-medium text-muted-foreground">
            {{ viewType === 'organization' ? 'まだ受験した先生はいません' : 'まだ受験履歴がありません' }}
          </p>
        </div>

        <div v-else class="border rounded-lg overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-muted border-b">
              <tr>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">受験日時</th>
                <th v-if="viewType === 'organization'" class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">先生</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">得点</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground">結果</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="a in attempts"
                :key="a.id"
                class="odd:bg-white even:bg-muted/30 border-b last:border-b-0"
              >
                <td class="px-4 py-2.5 text-sm text-muted-foreground">{{ a.submitted_at }}</td>
                <td v-if="viewType === 'organization'" class="px-4 py-2.5 text-sm font-medium">{{ a.member_name }}</td>
                <td class="px-4 py-2.5 text-sm">{{ a.correct_count }} / {{ a.total_questions }}問正解</td>
                <td class="px-4 py-2.5">
                  <Badge v-if="a.is_passed" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-100">合格</Badge>
                  <Badge v-else variant="destructive" class="opacity-80">不合格</Badge>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>
  </AppLayout>
</template>

<script setup>
import { ClipboardList, Info } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Badge } from '@/components/ui/badge'

defineProps({
  isEligible: { type: Boolean, default: true },
  viewType:   { type: String, default: 'member' }, // 'organization' | 'member'
  attempts:   { type: Array, default: () => [] },
})
</script>