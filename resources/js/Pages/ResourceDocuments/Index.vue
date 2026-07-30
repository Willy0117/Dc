<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">資料</p>
      <h1 class="text-xl font-semibold">資料一覧</h1>
    </template>

    <div class="p-6 space-y-6">
      <div v-if="categories.length === 0" class="py-16 text-center text-muted-foreground">
        <FileText class="w-8 h-8 mx-auto mb-2 opacity-30" />
        資料はまだありません
      </div>

      <div v-for="cat in categories" :key="cat.id" class="space-y-2">
        <h2 class="text-sm font-semibold text-muted-foreground">{{ cat.name }}</h2>
        <div class="border rounded-lg overflow-hidden">
          <div
            v-for="doc in cat.documents"
            :key="doc.id"
            class="flex items-center justify-between px-4 py-3 odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b last:border-b-0 transition-colors"
          >
            <component
              :is="isDoc(doc) ? 'div' : 'button'"
              type="button"
              class="flex items-center gap-3 min-w-0 text-left flex-1"
              @click="isDoc(doc) ? null : openPreview(doc)"
            >
              <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" :class="iconBg(doc)">
                <component :is="iconFor(doc)" class="w-4.5 h-4.5" :class="iconColor(doc)" />
              </div>
              <div class="min-w-0">
                <p class="text-sm font-medium truncate" :class="{ 'hover:underline': !isDoc(doc) }">{{ doc.title }}</p>
                <p class="text-xs text-muted-foreground">
                  {{ (doc.extension || 'pdf').toUpperCase() }}<span v-if="doc.file_size"> ・ {{ formatFileSize(doc.file_size) }}</span>
                </p>
              </div>
            </component>

            <div class="flex items-center gap-2 shrink-0">
              <button
                v-if="!isDoc(doc)"
                type="button"
                class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-muted-foreground border border-border rounded-lg hover:bg-muted"
                @click="openPreview(doc)"
              >
                <Eye class="w-3.5 h-3.5" />確認
              </button>
              <a
                :href="doc.file_url"
                target="_blank"
                class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-primary border border-primary/30 rounded-lg hover:bg-primary/5"
              >
                <Download class="w-3.5 h-3.5" />ダウンロード
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- プレビュー モーダル（PDF・JPEGのみ、Docは対象外） -->
    <Teleport to="body">
      <div v-if="previewDoc" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closePreview">
        <div class="w-full max-w-4xl h-[85vh] bg-white rounded-lg shadow-xl overflow-hidden flex flex-col">
          <div class="flex items-center justify-between px-4 py-3 border-b shrink-0">
            <p class="text-sm font-medium truncate pr-2">{{ previewDoc.title }}</p>
            <div class="flex items-center gap-2 shrink-0">
              <a
                :href="previewDoc.file_url"
                target="_blank"
                class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-primary border border-primary/30 rounded-lg hover:bg-primary/5"
              >
                <Download class="w-3.5 h-3.5" />ダウンロード
              </a>
              <button type="button" class="text-muted-foreground hover:text-foreground p-1" @click="closePreview">
                <X class="w-5 h-5" />
              </button>
            </div>
          </div>
          <div class="flex-1 bg-muted flex items-center justify-center overflow-auto">
            <img
              v-if="isImage(previewDoc)"
              :src="previewDoc.file_url"
              class="max-w-full max-h-full object-contain"
            />
            <iframe
              v-else
              :src="previewDoc.file_url"
              class="w-full h-full"
              frameborder="0"
            />
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { FileText, FileType, Image as ImageIcon, Download, Eye, X } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  categories: { type: Array, default: () => [] },
})

const previewDoc = ref(null)

function ext(doc) {
  return (doc?.extension || '').toLowerCase()
}

function isImage(doc) {
  return ['jpg', 'jpeg'].includes(ext(doc))
}

function isDoc(doc) {
  return ['doc', 'docx'].includes(ext(doc))
}

function iconFor(doc) {
  if (isImage(doc)) return ImageIcon
  if (isDoc(doc)) return FileType
  return FileText
}

function iconColor(doc) {
  if (isImage(doc)) return 'text-blue-600'
  if (isDoc(doc)) return 'text-sky-700'
  return 'text-red-600'
}

function iconBg(doc) {
  if (isImage(doc)) return 'bg-blue-50'
  if (isDoc(doc)) return 'bg-sky-50'
  return 'bg-red-50'
}

function openPreview(doc) {
  if (isDoc(doc)) return // Wordファイルはプレビュー非対応、ダウンロードのみ
  previewDoc.value = doc
}

function closePreview() {
  previewDoc.value = null
}

function formatFileSize(bytes) {
  if (!bytes) return '-'
  const mb = bytes / (1024 * 1024)
  return mb >= 1024 ? `${(mb / 1024).toFixed(2)} GB` : `${mb.toFixed(1)} MB`
}
</script>