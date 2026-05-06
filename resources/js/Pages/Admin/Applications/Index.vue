<template>
  <AppLayout>
    <template #header>{{ t('applications.list') }}</template>
    <div dir="rtl">
      <!-- 検索 トリガーボタン -->
        <div class="relative size-4 ...">
          <div class="absolute start-0 top-0 size-14 ...">
              <button
              @click="openDrawer = true"
              class="p-2 rounded hover:bg-gray-200 flex items-center justify-center"
            >
              <MagnifyingGlassIcon class="w-5 h-5 text-gray-600" />
            </button>
          </div>
        </div>
    </div>
    <div class="p-6">
      <!-- 右側 Drawer -->
      <div v-if="openDrawer" class="fixed inset-0 z-40">
        <!-- 背景オーバーレイ -->
        <div class="absolute inset-0 bg-black bg-opacity-30" @click="openDrawer = false"></div>

        <!-- 右側 Drawer -->
        <aside
          class="absolute top-0 right-0 h-full bg-white shadow-lg z-50 flex flex-col transition-all duration-300 overflow-hidden"
          :style="{ width: openDrawer ? '20rem' : '0rem' }"
        >      
          <div class="p-4 flex justify-between items-center border-b">
            <h2 class="text-lg font-bold">{{ t('search') }}</h2>
            <button @click="openDrawer = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <div class="p-4 space-y-3">
            <select v-if="isSuperAdmin" v-model="form.tenant_id" class="border rounded px-3 py-2 w-full">
              <option value="">{{ t('please_select') }}</option>
              <option v-for="t in tenants" :key="t.id" :value="t.id">
                {{ t.name }}
              </option>
            </select>
            <!-- 既存 form をそのまま利用 -->
            <input v-model="form.code" type="text" :placeholder="t('code')" class="border rounded px-3 py-2 w-full" />

            <!-- 喪主様情報 -->
            <div class="col-span-4 sm:col-span-2 border-gray-300">
                <InputLabel for="name" :value="t('applications.name')" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" />
            </div>
            <div class="col-span-2 sm:col-span-2">
                <InputLabel for="delivery_date" :value="t('registers.delivery_date')" />
                <TextInput
                    v-model="form.delivery_date"
                    type="datetime-local"
                    :min="minFuneralDatetime"
                    class="mt-1 block w-full"
                />
            </div>           
            <div class="col-span-2 sm:col-span-2">
                <InputLabel for="application_date" :value="t('registers.application_date')" />
                <TextInput
                    v-model="form.application_date"
                    type="datetime-local"
                    :min="minFuneralDatetime"
                    class="mt-1 block w-full"
                />
            </div>           
            <div class="flex justify-end space-x-2 mt-4">
              <button @click="submitSearch(); openDrawer = false"
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                {{ t('search') }}
              </button>
              <button @click="openDrawer = false"
                      class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                {{ t('close') }}
              </button>
            </div>
          </div>
        </aside>
      </div>       

      <div class="flex flex-wrap md:flex-nowrap md:justify-between mb-4 items-center gap-2 text-sm">

        <!-- per_page + add -->
        <div class="flex items-center gap-2">
          <select
            v-model.number="form.per_page"
            @change="submitSearch"
            class="border rounded px-3 py-2 w-16 h-10"
          >
            <option v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</option>
          </select>

        </div>

        <!-- 複数削除ボタン -->
        <!-- button
          @click="bulkDelete"
          :disabled="selectedIds.length === 0"
          class="px-4 h-10 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50 flex items-center space-x-1"
        >
          <TrashIcon class="w-4 h-4"/>
          <span>{{ t('delete_selected') }}</span>
        </button -->
      </div>

      <!-- 会員一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <!--th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th -->
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('id')">
              {{ t('applications.code') }}
              <span v-if="form.sort_by==='id'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('apply_type')">
              {{ t('applications.apply_type') }}
              <span v-if="form.sort_by==='apply_type'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('organization_id')">
              {{ t('applications.organization') }}
              <span v-if="form.sort_by==='organization_id'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('name')">
              {{ t('applications.name') }}
              <span v-if="form.sort_by==='name'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('deceased_furigana')">
              {{ t('applications.furigana') }}
              <span v-if="form.sort_by==='deceased_furigana'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('age_at_death')">
              {{ t('applications.age_at_death') }}
              <span v-if="form.sort_by==='age_at_death'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('gender')">
              {{ t('applications.gender') }}
              <span v-if="form.sort_by==='gender'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('delivery_date')">
              {{ t('applications.delivery_date') }}
              <span v-if="form.sort_by==='delivery_date'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('created_at')">
              {{ t('applications.application_date') }}
              <span v-if="form.sort_by==='created_at'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('status')">
              {{ t('applications.status') }}
              <span v-if="form.sort_by==='status'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 text-center">{{ t('actions.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="application in applications.data" :key="application.id" class="odd:bg-white even:bg-gray-100">

            <!-- td class="px-3 py-2">
              <input type="checkbox" :value="application.id" v-model="selectedIds" />
            </td -->
            <td>
              <span v-if="application.pdf_documents?.length">
                <button
                  @click="previewPdf = application.pdf_documents[0]?.url"
                  class="text-blue-600 hover:underline"
                >
                  {{ application.order_code }}
                </button>
              </span>
              <span v-else>
                {{ application.order_code }}
              </span>
            </td>
            <td class="px-3 py-2">{{ application.apply_type ?? '-' }}</td>
            <td class="px-3 py-2">{{ application.organization?.name ?? '-' }} {{ application.organization?.abbr ?? '-' }}</td>
            <td class="px-3 py-2">{{ application.fullname ?? '-' }}</td>
            <td class="px-3 py-2">{{ application.deceased_furigana ?? '-' }}</td>
            <td class="px-3 py-2 text-right">{{ application.age_at_death ?? '-' }}</td>
            <td class="px-3 py-2">{{ application.gender ?? '-' }}</td>
            <td class="px-3 py-2">{{ application.delivery_date ? dayjs(application.delivery_date).format('YYYY/MM/DD HH:mm') : '' }}</td>
            <td class="px-3 py-2">{{ application.created_at ? dayjs(application.created_at).format('YYYY/MM/DD HH:mm') : '' }}</td>
            <td class="px-3 py-2">
              <span
                class="cursor-pointer text-blue-600 hover:underline"
                @click="openStatus(application)"
              >
                {{ application.status }}
              </span>
            </td>
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <Link :href="route('admin.applications.show', { application: application.id, ...persistQuery() })"
                class="text-blue-500 hover:text-blue-700 flex items-center space-x-1 text-sm px-2 py-1">
                <DocumentIcon class="w-4 h-4"/><span>{{ t('applications.document') }}</span>
              </Link>
              <button
                @click="openUpload(application)"
                class="text-green-500 hover:text-green-700 flex items-center space-x-1 text-sm px-2 py-1"
              >
                <DocumentPlusIcon class="w-4 h-4"/><span>{{ t('applications.delivery') }}</span>
              </button>
              <!-- 印刷 -->
              <button
                @click="openPrint(application)"
                :disabled="application.status !== '完了'"
                :class="[
                  'flex items-center space-x-1 text-sm px-2 py-1',
                  application.status === '完了'
                    ? 'text-purple-500 hover:text-purple-700'
                    : 'text-gray-300 cursor-not-allowed'
                ]"
              >
                <PrinterIcon class="w-4 h-4"/>
                <span>{{ t('applications.print') }}</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="applications" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>

  <div>
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

      <DialogModal :show="showStatusModal" @close="closeModal">
        <template #title>
            {{ t('applications.status_change') }}
        </template>

        <template #content>
          <select
            v-model="statusForm.status"
            class="w-full border rounded px-3 py-2 mb-4"
          >
            <option
              v-for="s in statusOptions"
              :key="s.value"
              :value="s.value"
            >
              {{ s.label }}
            </option>
          </select>
          <!-- 日付入力 -->
          <input
            type="datetime-local"
            v-model="statusForm.date"
            class="w-full border rounded px-3 py-2 mb-4"
          />
        </template>
        <template #footer>
          <SecondaryButton @click="closeModal">
            <v-spacer />
            <v-btn text @click="showStatusModal = false">{{ t('cancel') }}</v-btn>
          </SecondaryButton>
          <PrimaryButton class="ms-3" @click="submitStatus">
              {{ t('actions.update') }}
          </PrimaryButton>
        </template>
      </DialogModal>

      <DialogModal :show="showProgressModal" @close="closeModal">
        <template #title>
          {{ t('applications.progress_change') }}
        </template>
        <template #content>
            <select
              v-model="progressForm.progress_id"
              class="w-full border rounded px-3 py-2"
            >
              <option
                v-for="p in progresses"
                :key="p.id"
                :value="p.id"
              >
                {{ p.name }}
              </option>
            </select>
        </template>
        <template #footer>
          <SecondaryButton @click="closeModal">
            <v-spacer />
            <v-btn text @click="showProgressModal = false">{{ t('cancel') }}</v-btn>
          </SecondaryButton>
          <PrimaryButton class="ms-3" @click="submitProgress">
              {{ t('actions.update') }}
          </PrimaryButton>
        </template>
      </DialogModal>

      <DialogModal :show="showUploadModal" @close="closeModal">
        <template #title>{{ t('applications.documents') }}</template>

        <template #content>
          <!-- ドラッグ＆ドロップ領域 -->
          <div
            class="mt-4 min-h-80 p-4 bg-[#e7dfc8] border-2 border-dashed border-gray-300 rounded text-center cursor-pointer hover:border-gray-500"
            @dragover.prevent
            @dragenter.prevent
            @drop.prevent="handleDrop"
            @click="fileInput.click()"
          >
            <p v-if="!file">{{ t('applications.uploads') }}</p>
            <p v-else class="text-sm text-gray-700">{{ t('selected') }} {{ file.name }}</p>

            <!-- hidden file input -->
            <input
              type="file"
              ref="fileInput"
              class="hidden"
              accept="image/png"
              @change="onFileChange"
            />
          </div>
        </template>

        <template #footer>
          <div class="flex justify-end gap-3">
            <SecondaryButton @click="closeModal">
              {{ t('cancel') }}
            </SecondaryButton>

            <PrimaryButton @click="submitUpload">
              {{ t('upload') }}
            </PrimaryButton>
          </div>
        </template>
      </DialogModal>      
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import axios from 'axios'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import { PrinterIcon, PlusIcon, DocumentIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentPlusIcon} from '@heroicons/vue/24/outline'
import { Inertia } from '@inertiajs/inertia'
import dayjs from 'dayjs'

const page = usePage()

console.log(page)

const user = page.props.auth.admin ?? page.props.auth.user

const props = defineProps({
  applications: Object,
  tenants: Array,
  statusOptions: Array,
  filters: {
    type: Object,
    default: () => ({
      code: '', name: '', delivary_date: '', status_id: 1,tenant_id: '',
      per_page: 20, sort_by: 'created_at', sort_dir: 'desc', page: 1
    })
  },
})

console.log(props.applications)

const { t } = useI18n()

// 検索フォーム・per_page・sort・sort_dirを reactive で管理
const openDrawer = ref(false)

// 複数検索用に reactive 拡張
const form = reactive({
  name: props.filters.name,
  delivary_date: props.filters.delivery_date,
  application_date: props.filters.application_date,
  code: props.filters.code,
  status_id: props.filters.status_id,
  tenant_id: props.filters.tenant_id,
  per_page: props.filters.per_page || 20,
  sort_by: props.filters.sort_by,   // ← 初期値を必ずセット
  sort_dir: props.filters.sort_dir,    // ← 初期値を必ずセット
})
// 選択削除
const selectedIds = ref([])

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.applications.data.map(s => s.id) : []
}

const resetSelectedIds = () => {
  selectedIds.value = []
}

const selectAll = computed({
  get() {
    return selectedIds.value.length === props.applications.data.length
  }
})

watch(() => props.applications.current_page, () => {
  selectedIds.value = []
})


// persistQueryに各検索項目を追加
const persistQuery = () => ({
  tenant_id: form.tenant_id,
  code: form.code,
  name: form.name,
  delivary_date: form.delivery_date,
  application_date: form.application_date,
  gender: form.gendar,
  status_id: form.status_id,
  per_page: form.per_page,
  sort_by: form.sort_by,
  sort_dir: form.sort_dir,
  page: props.applications.current_page
})

const submitSearch = () => {
  console.log(persistQuery())
  router.get(route('admin.applications.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// ページ番号クリック
const goPage = (page) => {
  router.get(route('admin.applications.index'), { ...persistQuery(), page }, {
    preserveState: true,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// 列ヘッダクリックでソート
const sortBy = (field) => {
  if (form.sort_by === field) form.sort_dir = form.sort_dir==='asc'?'desc':'asc'
  else { form.sort_by = field; form.sort_dir = 'desc' }
  submitSearch()
}

// 行単位削除
const deleteapplication = (application_id) => {
  if (!confirm(t('confirm_delete'))) return
  router.delete(route('admin.applications.destroy', application_id), {
    preserveState: true,
    onSuccess: () => {
      router.get(route('admin.applications.index'), { ...persistQuery(), page: props.applications.current_page }, { preserveState: true })
    }
  })
}
// 複数削除
const bulkDelete = () => {
  if (!confirm(t('confirm_delete_selected'))) return
  router.post(
    route('applications.bulkDelete'),
    { ids: selectedIds.value },
    {
      preserveState: true,
      onSuccess: () => {
        // 削除後に検索条件・ページを保持して再取得
        router.get(route('admin.applications.index'), { ...persistQuery(), page: props.applications.current_page }, { preserveState: true })
      }
    }
  )
}
const previewPdf = ref(null)

const openPdf = (pdfPath) => {
  console.log('PDF PATH:', pdfPath)
  if (!pdfPath) return

  // 例：フルパス化
  previewPdf.value = pdfPath

  // 例：ここで loading true
}

// 表示件数計算
const startItem = computed(() => props.applications.per_page * (props.applications.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.applications.per_page * props.applications.current_page, props.applications.total))

const showProgressModal = ref(false)

const progressForm = reactive({
  application_id: null,
  progress_id: null,
})


const progresses = ref([])

const openProgress = async (application) => {
  // ★ axios より前で判定
  if (application.status.id !== 1) {
    alert('申請中のデータのみ進捗を訂正できます。')
    return
  }

  // ここから先だけ axios
  const res = await axios.get(
    `/admin/application/${application.id}/progress/edit`
  )

  progressForm.application_id = application.id
  progressForm.progress_id = res.data.application.progress_id
  progresses.value = res.data.progresses

  showProgressModal.value = true
}


const submitProgress = async () => {
  await axios.put(
    `/admin/application/${progressForm.application_id}/progress`,
    {
      progress_id: progressForm.progress_id,
    }
  )

  closeModal()

  // 一覧だけ再取得
  router.reload({ only: ['applications'] })
}

const showStatusModal = ref(false)

const statusForm = ref({
  application_id: null,
  status: '',
  date: '',
})

const statusMap = {
  '受付済': 'received',
  '作業中': 'working',
  '完了': 'completed',
}


const openStatus = async (application) => {
  statusForm.value.application_id = application.id
  statusForm.value.status = statusMap[application.status] || ''

  if (application.working_at) {
    statusForm.value.date = application.working_at
  } else if (application.completed_at) {
    statusForm.value.date = application.completed_at
  } else {
    statusForm.value.date = application.created_at
  }
  //statusForm.value.date = dayjs.tz(statusForm.value.date,'Asia/Tokyo').format('YYYY-MM-DDTHH:mm')
  showStatusModal.value = true
}

const submitStatus = async () => {

  const url = `/admin/applications/${statusForm.value.application_id}/status`

  await axios.put(
    url,
    { 
      status: statusForm.value.status,
      date: statusForm.value.date || new Date().toISOString().slice(0, 10),
    }
  )

  showStatusModal.value = false
  router.reload({ only: ['applications'] })
}

const closeModal = () => {
  showProgressModal.value = false;
  showStatusModal.value = false;
  showUploadModal.value = false
  file.value = null
  uploadForm.value.type_id = null
}
const showUploadModal = ref(false)

const uploadForm = ref({
  application_id: null,
  type_id: null,
})

const file = ref(null)

/**
 * モーダルを開く（status / progress と同型）
 */
const openUpload = (application) => {
  uploadForm.value.application_id = application.id
  showUploadModal.value = true
}

const fileInput = ref(null)

/**
 * drag & drop
 */
const handleDrop = (e) => {
  const droppedFiles = e.dataTransfer.files
  if (droppedFiles.length && droppedFiles[0].type === 'image/png') {
    file.value = droppedFiles[0]
  } else {
    alert('PNG ファイルを1つだけアップロードしてください')
  }
}

/**
 * file input
 */
const onFileChange = (e) => {
  const selected = e.target.files[0]
  if (selected && selected.type === 'image/png') {
    file.value = selected
  } else {
    alert('PNG ファイルを選択してください')
    file.value = null
  }
}

/**
 * submit（status と同型）
 */
const submitUpload = async () => {
  if (!file.value)
    return alert('アップするファイルを選択してください')

  const formData = new FormData()
  formData.append('document', file.value)   // ← controller と一致
  console.log(formData);

  try {
    await axios.post(
      `/admin/applications/${uploadForm.value.application_id}/upload-document`,
      formData,
    )

    alert('アップロード完了')
    showUploadModal.value = false
    router.reload({ only: ['applications'] })
  } catch (err) {
    console.error(err)
    alert('アップロード失敗')
  }
}

const openPrint = (application) => {
  Inertia.visit(
    route('admin.applications.printDocument', {
      application: application.id,...persistQuery(), 
    })
  )
}
</script>