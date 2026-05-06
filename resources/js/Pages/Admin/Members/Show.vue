<template>
  <AppLayout>
    <template #header>{{ t('members.detail') }}</template>

    <div class="space-y-6 p-6">

      <!-- 申請者 -->
      <section class="bg-white rounded shadow p-4">
        <h2 class="font-bold mb-2">申請者</h2>
        <p>{{ t('members.name')}} ： {{ props.member.name }}</p>
        <p>{{ t('status') }} ： {{ props.member.status.name }}</p>
        <p>{{ t('members.progress') }} : {{ props.member.progress?.name ?? '-' }}</p>
      </section>

      <!-- 法人 -->
    <section class="bg-white rounded shadow p-4">
        <h2 class="font-bold mb-2">法人情報</h2>

        <div v-for="org in props.member.organizations" :key="org.id" class="mb-4">
            <p>法人名：{{ org.name }}</p>

            <div class="mt-2">
            <h3 class="font-semibold">住所</h3>
            <ul>
                <li>
                {{ org.postal_code }}  {{ org.address }}
                </li>
            </ul>

            <p>{{ t('members.tel') }}: {{ org.tel }} {{ t('members.fax') }} : {{ org.fax }}</p>
            <p v-if="org.mobile">{{ t('members.mobile') }} : {{ org.mobile }}</p>
            <p v-if="org.email">{{ t('members.email') }} : {{ org.email }}</p>
            <p v-if="org.contact_name">{{ t('members.staff') }}: {{ org.contact_name }}</p>
            </div>
        </div>
    </section>

    <!-- 書類 -->
    <section class="bg-white rounded shadow p-4">
        <h2 class="font-bold mb-2">提出書類</h2>
        <div class="flex gap-4">
            <div v-for="d in props.member.documents" :key="d.type + d.path">
            <img
                v-if="d.thumbnail_path"
                :src="d.thumbnail_path"
                class="w-24 cursor-pointer"
                @click="openPdf(d.path)"
            />
            </div>
        </div>
    </section>
    <div class="flex gap-2">
        <!-- 編集ボタン -->
        <Link
        :href="route('admin.member.edit', { member: props.member.id, ...persistQuery() })"
        class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
        >
        <PencilIcon class="w-4 h-4 mr-2"/>
        {{ t('actions.edit') }}
        </Link>

        <!-- キャンセル/戻るボタン -->
        <Link
        :href="route('admin.member.index', persistQuery())"
        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"
        >
        <ArrowLeftIcon class="w-4 h-4 mr-2"/>
        {{ t('actions.cancel') }}
        </Link>
    </div>
    </div>
    
    <div>
      <DialogModal
        :show="!!previewPdf"
        maxWidth="7xl"
        @close="previewPdf = null"
      >
        <template #title>
          PDF プレビュー
        </template>

        <template #content>
          <div class="w-[90vw] h-[80vh]">
            <iframe
              v-if="previewPdf"
              :src="previewPdf"
              class="w-full h-full border"
            />
          </div>
        </template>

        <template #footer>
          <SecondaryButton @click="previewPdf = null">
            閉じる
          </SecondaryButton>
        </template>
      </DialogModal>
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentDuplicateIcon, ArrowLeftIcon} from '@heroicons/vue/24/outline'

const { t } = useI18n()

const props = defineProps({
  member: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

console.log(props.member)
// persistQueryに各検索項目を追加
const persistQuery = () => {
  return { ...props.filters }
}

const previewPdf = ref(null)

const openPdf = (pdfPath) => {
  console.log('PDF PATH:', pdfPath)
  if (!pdfPath) return

  // 例：フルパス化
  previewPdf.value = pdfPath

  // 例：ここで loading true
}

</script>
