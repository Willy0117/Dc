<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Check, AlertCircle, Plus } from 'lucide-vue-next'
import dayjs from 'dayjs'

interface LicenseFeeMaster {
  id: number
  corporate_fee: number | null
  personal_fee: number | null
  started_at: string
}

defineProps<{
  masters: LicenseFeeMaster[]
  current: LicenseFeeMaster | null
}>()

const form = ref({
  corporate_fee: '',
  personal_fee: '',
  started_at: '',
})

const saving = ref(false)

function handleSubmit() {
  if (!confirm('新しい料金を登録します。よろしいですか？')) return
  saving.value = true
  router.post('/admin/license-fees', form.value, {
    onSuccess: () => {
      form.value = { corporate_fee: '', personal_fee: '', started_at: '' }
    },
    onFinish: () => { saving.value = false },
  })
}
</script>

<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">マスター設定</p>
      <h1 class="text-xl font-semibold">ライセンス料</h1>
    </template>

    <div class="max-w-5xl mx-auto px-6 py-6 space-y-6">

      <!-- 現在の料金 -->
      <div v-if="current" class="rounded-lg border bg-background p-6">
        <h2 class="text-sm font-semibold mb-3">現在の料金</h2>
        <div class="grid grid-cols-3 gap-4 text-sm">
          <div>
            <p class="text-xs text-muted-foreground">法人ライセンス料</p>
            <p class="text-lg font-semibold">{{ current.corporate_fee?.toLocaleString() }} 円</p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">個人ライセンス料</p>
            <p class="text-lg font-semibold">{{ current.personal_fee?.toLocaleString() }} 円</p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">適用開始日</p>
            <p class="text-lg font-semibold">{{ dayjs(current.started_at).format('YYYY/MM/DD') }}</p>
          </div>
        </div>
      </div>
      <div v-else class="rounded-lg border bg-amber-50 border-amber-200 p-4 text-sm text-amber-800 flex items-center gap-2">
        <AlertCircle class="w-4 h-4 shrink-0" />
        料金が未設定です。以下から登録してください。
      </div>

      <!-- 新料金登録フォーム -->
      <div class="rounded-lg border bg-background p-6 space-y-4">
        <h2 class="text-sm font-semibold">新しい料金を登録</h2>
        <div class="grid grid-cols-3 gap-4">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">法人ライセンス料 <span class="text-destructive">*</span></Label>
            <Input v-model="form.corporate_fee" type="number" min="0" placeholder="例: 50000" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">個人ライセンス料 <span class="text-destructive">*</span></Label>
            <Input v-model="form.personal_fee" type="number" min="0" placeholder="例: 10000" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">適用開始日 <span class="text-destructive">*</span></Label>
            <Input v-model="form.started_at" type="date" />
          </div>
        </div>
        <Button
          type="button"
          :disabled="!form.corporate_fee || !form.personal_fee || !form.started_at || saving"
          class="bg-[#0C447C] hover:bg-[#185FA5] text-white gap-1.5"
          @click="handleSubmit"
        >
          <Plus class="w-4 h-4" />
          {{ saving ? '登録中...' : '料金を登録' }}
        </Button>
      </div>

      <!-- 改定履歴 -->
      <div class="rounded-lg border bg-background overflow-hidden">
        <div class="px-4 py-3 border-b bg-muted/30">
          <h2 class="text-sm font-semibold">改定履歴</h2>
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b bg-muted/20">
              <th class="text-right px-4 py-2 text-xs text-muted-foreground font-medium">法人料金</th>
              <th class="text-right px-4 py-2 text-xs text-muted-foreground font-medium">個人料金</th>
              <th class="text-right px-4 py-2 text-xs text-muted-foreground font-medium">適用開始日</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in masters" :key="m.id" class="border-b last:border-0 hover:bg-muted/10">
              <td class="px-4 py-2 text-right">
                {{ m.corporate_fee != null ? m.corporate_fee.toLocaleString() + ' 円' : '—' }}
              </td>
              <td class="px-4 py-2 text-right">
                {{ m.personal_fee != null ? m.personal_fee.toLocaleString() + ' 円' : '—' }}
              </td>
              <td class="px-4 py-2 text-right text-muted-foreground">
                {{ dayjs(m.started_at).format('YYYY/MM/DD') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AppLayout>
</template>
