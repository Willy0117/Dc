<!-- resources/js/Pages/Admin/PdfUploads/Index.vue -->
<template>
  <AppLayout :title="t('pdf-uploads.list')">
    <template #header>{{ t('pdf-uploads.list') }}</template>

    <div class="p-6 space-y-6">

      <!-- 検索・フィルター -->
      <div class="grid grid-cols-6 gap-4 text-sm">
        <div class="col-span-1">
          <InputLabel :value="t('pdf-uploads.category')" />
          <select v-model="form.category_id" class="input w-full border rounded px-2 py-1">
            <option value="">{{ t('all') }}</option>
            <option
                v-for="category in props.categories"
                :key="category.id"
                :value="category.id"
            >
                {{ category.name }}
            </option>
          </select>
        </div>
        <div class="col-span-2">
          <InputLabel :value="t('pdf-uploads.conference')" />
          <select v-model="form.conference_id" class="input w-full border rounded px-2 py-1">
            <option value="">{{ t('all') }}</option>
            <option
                v-for="conference in props.conferences"
                :key="conference.id"
                :value="conference.id"
            >
                {{ conference.name }}
            </option>
          </select>
        </div>
        <div class="col-span-1">
          <InputLabel :value="t('pdf-uploads.role')" />
          <select v-model="form.role_id" class="input w-full border rounded px-2 py-1">
            <option value="">{{ t('all') }}</option>
            <option
                v-for="role in filteredRoles"
                :key="role.id"
                :value="role.id"
            >
                {{ role.name }}
            </option>
          </select>
        </div>
        <div class="col-span-1"></div>
        <div class="flex items-end col-span-1">
          <button
            @click="submitSearch"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full"
          >
            {{ t('search') }}
          </button>
        </div>
      </div>

      <!-- PDF一覧テーブル -->
      <table class="min-w-full border border-gray-300 border-collapse">

        <thead>
          <tr class="bg-gray-200 text-xs">
            <th class="px-3 py-2">{{ t('members.name') }}</th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('credit_category_id')">
              {{ t('pdf-uploads.category') }}
              <span v-if="form.sort_by==='credit_category_id'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('session')">
              {{ t('pdf-uploads.session') }}
              <span v-if="form.sort_by==='session'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('credit_confarence_id')">
              {{ t('pdf-uploads.conference') }}
              <span v-if="form.sort_by==='credit_confarence_id'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('credit_role_id')">
              {{ t('pdf-uploads.role') }}
              <span v-if="form.sort_by==='credit_role_id'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2">{{ t('pdf-uploads.pdf') }}</th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('status')">
              {{ t('status') }}
              <span v-if="form.sort_by==='status'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2">{{ t('created_at') }}</th>
            <th class="px-3 py-2">{{ t('actions.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="upload in props.uploads.data" :key="upload.id" class="odd:bg-white even:bg-gray-100 text-sm">

            <td>{{ upload.member?.name ?? '-' }}</td>
            <td class="px-3 py-2">{{ upload.credit_category ? upload.credit_category.name : '' }}</td>
            <td class="px-3 py-2">{{ upload.session ? upload.session : '-' }}</td>
            <td class="px-3 py-2">{{ upload.credit_conference ? upload.credit_conference.name : '' }}</td>
            <td class="px-3 py-2">{{ upload.credit_role ? upload.credit_role.role : '' }}</td>
            <td class="px-3 py-2">
                <div
                    @click="openPreview(upload.id)"
                    class="cursor-pointer flex items-center gap-2"
                >
                    <img
                    v-if="upload.thumbnail_path"
                    :src="`/admin/pdf-uploads/${upload.id}/thumbnail`"
                    alt="Thumbnail"
                    class="w-10 h-10 object-contain border"
                    />
                    <span v-else class="text-gray-400 text-xs">
                    {{ t('no_thumbnail') }}
                    </span>
                </div>
            </td>
            <td class="px-3 py-2">{{ upload.updated_at ? dayjs(upload.updated_at).format('YYYY/MM/DD') : '' }}</td>
            <td class="px-3 py-2">
              <span
                class="text-xs px-1 rounded"
                :class="{
                  'bg-yellow-200 text-yellow-800': upload.status==='pending',
                  'bg-green-200 text-green-800': upload.status==='approved',
                  'bg-red-200 text-red-800': upload.status==='rejected'
                }"
              >
                {{ $t(upload.status) }}
              </span>
            </td>
            <td class="px-3 py-2 space-x-2">
              <button
                v-if="upload.status==='pending'"
                @click="approve(upload.id)"
                class="px-2 py-1 rounded text-xs hover:bg-green-600"
              >
                <HandThumbUpIcon class="w-5 h-5 text-green-500" />
              </button>
              <button
                v-if="upload.status==='pending'"
                @click="openReject(upload)"
                class="px-2 py-1 rounded text-xs hover:bg-red-600"
              >
                <HandThumbDownIcon class="w-5 h-5 text-red-500" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="uploads" :onPageChange="goPage"/>
    </div>

    <div>
      <DialogModal :show="showRejectModal" @close="closeModal">
        <template #title>
          {{ t('status') }} {{ t('change') }}
        </template>

        <template #content>
            <textarea
            v-model="rejectForm.message"
            class="w-full border rounded p-2 mb-4"
            rows="4"
            :placeholder="t('pdf-uploads.message')"
            >
            </textarea>
        </template>
        <template #footer>
          <SecondaryButton @click="closeModal">
            {{ t('cancel') }}
          </SecondaryButton>
          <PrimaryButton class="ms-3" @click="submitReject">
              {{ t('reject') }}
          </PrimaryButton>
        </template>
      </DialogModal>

      <DialogModal
        :show="!!previewPdf"
        maxWidth="7xl"
        @close="previewPdf = null"
      >
        <template #title>
          {{ t('PDFpreview') }}
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
            {{ t('closed') }}
          </SecondaryButton>
        </template>
      </DialogModal>
    </div> 
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import axios from 'axios'
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'

import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentPlusIcon, HandThumbUpIcon, HandThumbDownIcon} from '@heroicons/vue/24/outline'

const page = usePage()

const props = defineProps({
  uploads: Object,
  categories: Object,
  conferences: Object,
  roles: Object,
  filters: Object,
})

console.log(props.uploads.data)

const { t } = useI18n()


// フィルタフォーム
const form = reactive({
  category_id: props.filters?.category_id ?? '',
  conference_id: props.filters?.conference_id ?? '',
  role_id: props.filters?.role_id ?? '',
  status: props.filters?.status,
  per_page: props.filters?.per_page,
  sort_by: props.filters?.sort_by ?? 'created_at',
  sort_dir:props.filters?.sort_dir ?? 'desc',
})

const filteredRoles = computed(() =>
  props.roles.filter(r =>
    r.credit_category_id == form.category_id &&
    r.credit_conference_id == form.conference_id
  )
)

// ページング
const goPage = (page) => {
  router.get(route('admin.pdf-uploads.index'), {...form, page}, {preserveState:true})
}

// 検索
const submitSearch = () => {
  router.get(route('admin.pdf-uploads.index'), form, {preserveState:false})
}

// ソート
const sortBy = (field) => {
  if(form.sort_by===field) form.sort_dir=form.sort_dir==='asc'?'desc':'asc'
  else { form.sort_by=field; form.sort_dir='asc' }
  submitSearch()
}

// 承認
const approve = (id) => {
  router.post(
    route('admin.pdf-uploads.approve', { pdf: id }),
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

const showRejectModal = ref(false)

// 差し戻しモーダル
const rejectForm = ref({
  uploadId: null,
  message: ''
})

const openReject = async (upload) => {
  rejectForm.value.uploadId = upload.id
  rejectForm.value.message = ''
  showRejectModal.value = true
}

const closeModal = () => {
  showRejectModal.value = false;
}

const submitReject = () => {
  if (!rejectForm.value.message) {
    alert('Rejection message is required')
    return
  }

  router.post(
    route('admin.pdf-uploads.reject', { pdf: rejectForm.value.uploadId }),
    { rejection_message: rejectForm.value.message },
    { preserveState: true }
  )

  showRejectModal.value = false
}
const previewPdf = ref(null)

const openPreview = (id) => {
  previewPdf.value = `/admin/pdf-uploads/${id}/view`
}

</script>


