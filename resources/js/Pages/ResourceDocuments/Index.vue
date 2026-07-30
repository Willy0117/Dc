<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">資料</p>
      <h1 class="text-xl font-semibold">資料一覧</h1>
    </template>

    <div class="p-6 space-y-4">

      <!-- カテゴリータブ -->
      <div v-if="categories.length > 0" class="flex flex-wrap gap-2">
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          class="px-4 py-1.5 rounded-lg border text-sm font-medium transition-colors"
          :class="activeCategoryId === cat.id
            ? 'bg-primary text-primary-foreground border-primary'
            : 'bg-background hover:bg-muted border-border text-muted-foreground'"
          @click="activeCategoryId = cat.id"
        >
          {{ cat.name }}
          <span class="ml-1 text-xs opacity-70">({{ cat.documents.length }})</span>
        </button>
      </div>

      <!-- 資料グリッド -->
      <div v-if="categories.length === 0" class="py-16 text-center text-muted-foreground">
        <FileText class="w-8 h-8 mx-auto mb-2 opacity-30" />
        資料はまだありません
      </div>
      <div v-else-if="currentDocuments.length === 0" class="py-16 text-center text-muted-foreground">
        <FileText class="w-8 h-8 mx-auto mb-2 opacity-30" />
        このカテゴリーの資料はまだありません
      </div>
      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div
          v-for="doc in currentDocuments"
          :key="doc.id"
          class="border rounded-lg overflow-hidden bg-white hover:shadow-md transition-shadow"
        >
          <component
            :is="isDoc(doc) ? 'div' : 'button'"
            type="button"
            class="w-full flex flex-col items-start text-left"
            @click="isDoc(doc) ? null : openPreview(doc)"
          >
            <div class="relative w-full aspect-square bg-muted flex items-center justify-center" :class="isDoc(doc) ? '' : 'cursor-pointer'">
              <div class="w-14 h-14 rounded-xl flex items-center justify-center" :class="iconBg(doc)">
                <component :is="iconFor(doc)" class="w-7 h-7" :class="iconColor(doc)" />
              </div>
            </div>
            <div class="p-2.5 w-full">
              <p class="text-xs font-medium line-clamp-2" :class="{ 'hover:underline': !isDoc(doc) }">{{ doc.title }}</p>
              <p class="text-[10px] text-muted-foreground mt-0.5">
                {{ (doc.extension || 'pdf').toUpperCase() }}<span v-if="doc.file_size"> ・ {{ formatFileSize(doc.file_size) }}</span>
              </p>
            </div>
          </component>

          <div class="flex items-center gap-1.5 px-2.5 pb-2.5">
            <button
              v-if="!isDoc(doc)"
              type="button"
              class="flex items-center gap-1 px-2 py-1 text-[10px] font-semibold text-muted-foreground border border-border rounded-md hover:bg-muted"
              @click="openPreview(doc)"
            >
              <Eye class="w-3 h-3" />確認
            </button>
            <a
              :href="doc.file_url"
              target="_blank"
              class="flex items-center gap-1 px-2 py-1 text-[10px] font-semibold text-primary border border-primary/30 rounded-md hover:bg-primary/5"
            >
              <Download class="w-3 h-3" />ダウンロード
            </a>
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
import { ref, computed } from 'vue'
import { FileText, FileType, Image as ImageIcon, Download, Eye, X } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  categories: { type: Array, default: () => [] },
})

const activeCategoryId = ref(props.categories[0]?.id ?? null)

const currentDocuments = computed(() => {
  const cat = props.categories.find(c => c.id === activeCategoryId.value)
  return cat?.documents ?? []
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