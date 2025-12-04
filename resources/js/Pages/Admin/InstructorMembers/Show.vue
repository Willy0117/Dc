<template>
  <AppLayout :title="`${member.name} - ${t('instructor_details')}`">
    <template #header>{{ member.name }}</template>
    <div class="p-6 space-y-4">
      <button
        @click="backToIndex"
        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
      >
        ← {{ t('back') }}
      </button>
      <!-- 会員情報 -->
      <div class="border rounded p-4">
        <div><strong>{{ t('name') }}:</strong> {{ member.name }}</div>
        <div><strong>{{ t('status') }}:</strong> {{ member.status }}</div>
        <div><strong>{{ t('update_period') }}:</strong> {{ cycle.start_date }} - {{ cycle.end_date }}</div>
        <div><strong>{{ t('total_points') }}:</strong> {{ cycle.total_points }}</div>
        <div><strong>{{ t('conference_count') }}:</strong> {{ cycle.conference_count }}</div>
      </div>

      <!-- アップロード一覧 -->
      <div>
        <h2 class="text-lg font-semibold mb-2">{{ t('uploaded_files') }}</h2>

        <div v-if="uploads.length === 0" class="text-gray-500">{{ t('no_uploads') }}</div>

        <div v-else class="grid grid-cols-3 gap-4">
          <div v-for="upload in uploads" :key="upload.id" class="border rounded p-2">
            <div class="text-sm font-medium">{{ upload.credit_conference_name }}</div>
            <div class="text-xs text-gray-500">{{ upload.role_name }} - {{ upload.category_name }}</div>

            <img
              v-if="upload.thumbnail_path"
              :src="`/pdf-uploads/${upload.id}/thumbnail`"
              alt="PDF Thumbnail"
              class="w-full h-32 object-contain my-2"
            />
            <div v-else class="w-full h-32 bg-gray-100 flex items-center justify-center my-2 text-gray-400 text-xs">
              {{ t('no_thumbnail') }}
            </div>

            <div class="flex justify-between items-center mt-1">
              <a
                :href="`/pdf-uploads/${upload.id}/view`"
                target="_blank"
                class="text-blue-600 hover:underline text-xs"
              >
                {{ t('view_pdf') }}
              </a>

              <span
                class="text-xs px-1 rounded"
                :class="{
                  'bg-yellow-200 text-yellow-800': upload.status==='pending',
                  'bg-green-200 text-green-800': upload.status==='approved',
                  'bg-red-200 text-red-800': upload.status==='rejected'
                }"
              >
                {{ t(upload.status) }}
              </span>
            </div>

            <div v-if="upload.status==='rejected'" class="text-xs text-red-600 mt-1">
              {{ upload.rejection_message }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { usePage, router, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// props の取り出し方法が間違っていた
const props = usePage()
console.log(props);

const member = props.props.member
const uploads = props.props.uploads
const filters = props.props.filters

// update cycle が無い場合に備えて
const cycle = member.update_cycles?.[0] || {
  start_date: '-',
  end_date: '-',
  total_points: 0,
  conference_count: 0
}

// Vue で扱いやすく成形
const uploadList = uploads.map(u => ({
  ...u,
  credit_conference_name: u.credit_conference?.name || '',
  category_name: u.credit_category?.name || '',
  role_name: u.credit_role?.role || ''
}))

function backToIndex() {
  console.log(props.filters?.page ?? 1)
  router.get(route('admin.instructorMembers.index'), {
    search: props.filters?.search ?? '',
    page: props.filters?.page ?? 1,
  })
}
</script>
