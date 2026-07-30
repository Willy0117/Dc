<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">ライセンス証</p>
      <h1 class="text-xl font-semibold">ライセンス証ダウンロード</h1>
    </template>

    <div class="p-6 max-w-2xl">
      <div v-if="license" class="border rounded-lg p-6 bg-white space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
            <Award class="w-6 h-6 text-amber-600" />
          </div>
          <div>
            <p class="text-sm font-semibold">ライセンス証が発行されています</p>
            <p class="text-xs text-muted-foreground">発行日：{{ license.issued_date }}</p>
          </div>
        </div>
        <a
          :href="license.file_url"
          target="_blank"
          class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-primary text-primary-foreground text-sm font-semibold rounded-lg hover:opacity-90"
        >
          <Download class="w-4 h-4" />ライセンス証をダウンロード
        </a>
      </div>

      <div v-else class="border rounded-lg p-8 bg-white text-center space-y-2">
        <AlertCircle class="w-8 h-8 mx-auto text-muted-foreground opacity-40" />
        <p class="text-sm font-medium text-muted-foreground">ライセンス証は未発行です</p>
        <p class="text-xs text-muted-foreground">発行までしばらくお待ちいただくか、運営事務局までお問い合わせください。</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Award, Download, AlertCircle } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  license: { type: Object, default: null },
})
</script>
