<!-- resources/js/Pages/Admin/PdfUploads/Index.vue -->
<template>
  <AppLayout :title="t('pdf_upload')">
    <template #header>{{ t('pdf_upload') }}</template>

    <div class="p-6 space-y-6">

      <!-- 検索・フィルター -->
      <div class="grid grid-cols-4 gap-4">
        <div>
          <label class="block mb-1">{{ t('category') }}</label>
          <select v-model="form.category" class="input w-full border rounded px-2 py-1">
            <option value="">{{ t('all') }}</option>
            <option value="conference">{{ t('conference') }}</option>
            <option value="seminar">{{ t('seminar') }}</option>
            <option value="journal">{{ t('journal') }}</option>
          </select>
        </div>

        <div>
          <label class="block mb-1">{{ t('role') }}</label>
          <input type="text" v-model="form.role" class="input w-full border rounded px-2 py-1" placeholder="Role"/>
        </div>

        <div>
          <label class="block mb-1">{{ t('organization_name') }}</label>
          <input type="text" v-model="form.organization_name" class="input w-full border rounded px-2 py-1" placeholder="Organization"/>
        </div>

        <div class="flex items-end">
          <button
            @click="submitSearch"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full"
          >
            {{ t('search') }}
          </button>
        </div>
      </div>

      <!-- PDF一覧テーブル -->
      <table class="min-w-full border border-gray-300 border-collapse text-sm">

        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">{{ t('member_name') }}</th>
            <th class="px-3 py-2">{{ t('email') }}</th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('organization_name')">
              {{ t('organization_name') }}
              <span v-if="form.sort==='organization_name'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('role')">
              {{ t('role') }}
              <span v-if="form.sort==='role'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('category')">
              {{ t('category') }}
              <span v-if="form.sort==='category'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2">{{ t('thumbnail') }}</th>
            <th class="px-3 py-2">{{ t('pdf') }}</th>
            <th class="px-3 py-2">{{ t('status') }}</th>
            <th class="px-3 py-2">{{ t('created_at') }}</th>
            <th class="px-3 py-2">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="upload in uploads" :key="upload.id" class="odd:bg-white even:bg-gray-100">
            <td>{{ upload.member ? upload.member.name : 'Unknown' }}</td>
            <td>{{ upload.member ? upload.member.email : '-' }}</td>
            <td class="px-3 py-2">{{ upload.organization_name }}</td>
            <td class="px-3 py-2">{{ upload.role }}</td>
            <td class="px-3 py-2">{{ upload.category }}</td>
            <td class="px-3 py-2">
              <img
                v-if="upload.thumbnail_path"
                :src="`/admin/pdf-uploads/${upload.id}/thumbnail`"
                alt="Thumbnail"
                class="w-10 h-10 object-contain"
              />
              <span v-else class="text-gray-400 text-xs">{{ t('no_thumbnail') }}</span>
            </td>
            <td class="px-3 py-2">
              <a
                :href="`/admin/pdf-uploads/${upload.id}/view`"
                target="_blank"
                class="text-blue-600 hover:underline text-xs"
              >
                {{ t('view_pdf') }}
              </a>
            </td>
            <td class="px-3 py-2">
              <span
                :class="{
                  'bg-yellow-200 text-yellow-800 px-1 rounded': upload.status==='pending',
                  'bg-green-200 text-green-800 px-1 rounded': upload.status==='approved',
                  'bg-red-200 text-red-800 px-1 rounded': upload.status==='rejected'
                }"
              >
                {{ t(upload.status) }}
              </span>
            </td>
            <td class="px-3 py-2">{{ upload.updated_at ? dayjs(upload.updated_at).format('YYYY/MM/DD') : '' }}</td>
            <td class="px-3 py-2 space-x-2">
              <button
                v-if="upload.status==='pending'"
                @click="approve(upload.id)"
                class="bg-green-500 text-white px-2 py-1 rounded text-xs hover:bg-green-600"
              >
                {{ t('approve') }}
              </button>
              <button
                v-if="upload.status==='pending'"
                @click="openRejectModal(upload)"
                class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600"
              >
                {{ t('reject') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="uploads" :onPageChange="goPage"/>

      <!-- 差し戻しモーダル -->
<!-- reject モーダル -->
<div v-if="rejectModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
  <div class="bg-white rounded-lg w-96 p-4">
    <h2 class="text-lg font-bold mb-2">{{ t('reject_pdf') }}</h2>

    <textarea
      v-model="rejectModal.message"
      class="w-full border rounded p-2 mb-4"
      rows="4"
      placeholder="Rejection message"
    ></textarea>

    <div class="flex justify-end space-x-2">
      <button
        class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
        @click="rejectModal.show=false"
      >
        {{ t('cancel') }}
      </button>

      <button
        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
        @click="submitReject"
      >
        {{ t('reject') }}
      </button>
    </div>
  </div>
</div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'

const props = defineProps({
  uploads: Object
})

const { t } = useI18n()

// フィルタフォーム
const form = reactive({
  category: '',
  role: '',
  organization_name: '',
  sort: 'id',
  direction: 'asc'
})

// ページング
const goPage = (page) => {
  router.get(route('admin.pdf_uploads.index'), {...form, page}, {preserveState:true})
}

// 検索
const submitSearch = () => {
  router.get(route('admin.pdf_uploads.index'), form, {preserveState:true})
}

// ソート
const sortBy = (field) => {
  if(form.sort===field) form.direction=form.direction==='asc'?'desc':'asc'
  else { form.sort=field; form.direction='asc' }
  submitSearch()
}

// 承認
const approve = (id) => {
  router.post(
    route('admin.pdf_uploads.approve', { pdf: id }),
    {},
    {
      preserveState:true,
      onSuccess: () => {
        const upload = props.uploads.find(u => u.id === id)
        if(upload) upload.status = 'approved'
      }
    }
  )
}

// 差し戻しモーダル
const rejectModal = reactive({
  show: false,
  uploadId: null,
  message: ''
})

const openRejectModal = (upload) => {
  rejectModal.show = true
  rejectModal.uploadId = upload.id
  rejectModal.message = ''
}

const submitReject = () => {
  if (!rejectModal.message) {
    alert('Rejection message is required')
    return
  }

  router.post(
    route('admin.pdf_uploads.reject', { pdf: rejectModal.uploadId }),
    { rejection_message: rejectModal.message },
    { preserveState: true }
  )

  rejectModal.show = false
}
</script>



