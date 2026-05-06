<template>
  <AppLayout>
    <template #header>{{ t('members.member_list') }}</template>
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
            <input v-model="form.name" type="text" :placeholder="t('name')" class="border rounded px-3 py-2 w-full" />
            <select v-model="form.process_id" class="border rounded px-3 py-2 w-full">
              <option value="">{{ t('please_select') }}</option>
              <option v-for="p in processes" :key="p.id" :value="p.id">
                {{ p.name }}
              </option>
            </select>
            <select v-model="form.measurement" class="border rounded px-3 py-2 w-full">
              <option :value="null">{{ t('please_select')}}</option>
              <option value="0">{{ t('dont') }}</option>
              <option value="1">{{ t('do') }}</option>
            </select>

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

      <div class="flex flex-wrap md:flex-nowrap md:justify-between mb-4 items-center gap-2">

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
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th v-if="isSuperAdmin">{{ t('tenant') }}</th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('name')">
              {{ t('members.name') }}
              <span v-if="form.sort_by==='name'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('tel')">
              {{ t('members.tel') }}
              <span v-if="form.sort_by==='tel'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('fax')">
              {{ t('members.fax') }}
              <span v-if="form.sort_by==='fax'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('address')">
              {{ t('members.address') }}
              <span v-if="form.sort_by==='address'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('email')">
              {{ t('members.email') }}
              <span v-if="form.sort_by==='email'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('created_at')">
              {{ t('updated_at') }}
              <span v-if="form.sort_by==='created_at'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 text-center">{{ t('actions.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in members.data" :key="member.id" class="odd:bg-white even:bg-gray-100">

            <td class="px-3 py-2">
              <input type="checkbox" :value="member.id" v-model="selectedIds" />
            </td>
            <td v-if="isSuperAdmin">
              {{ tenants.find(t => t.id === member.tenant_id)?.name || '-' }}
            </td>            
            <td class="px-3 py-2">{{ member.name ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.tel ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.fax ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.full_address ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.email ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.created_at ? dayjs(member.created_at).format('YYYY/MM/DD') : '' }}</td>
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <Link :href="route('admin.member.edit', { member: member.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="members" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>

    <div>
 
      <DialogModal :show="showStatusModal" @close="closeModal">
        <template #title>
          ステータス変更
        </template>

        <template #content>
          <select
            v-model="statusForm.status_id"
            class="w-full border rounded px-3 py-2"
          >
            <option
              v-for="s in statuses"
              :key="s.id"
              :value="s.id"
            >
              {{ s.name }}
            </option>
          </select>
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



   
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import axios from 'axios'
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentPlusIcon} from '@heroicons/vue/24/outline'

const page = usePage()

const props = defineProps({
  members: Object,
  user: Object,
  tenants: Array,
  statuses: Array,
  filters: {
    type: Object,
    default: () => ({
      name: '', tel: '', tenant_id: '', status_id: 1,
      per_page: 20, sort_by: 'created_at', sort_dir: 'desc', page: 1
    })
  }
})

console.log(props.members)

const { t } = useI18n()

const show = ref(false)
const message = ref('')
let timer = null

watch(
  () => page.props.flash.success,
  (val) => {
    if (val) {
      message.value = val
      show.value = true

      if (timer) clearTimeout(timer)

      timer = setTimeout(() => {
        show.value = false
      }, 3000)
    }
  },
  { immediate: true }
)

const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super admin')
)

// 検索フォーム・per_page・sort・sort_dirを reactive で管理
const openDrawer = ref(false)

// 複数検索用に reactive 拡張
const form = reactive({
  name: props.filters.name,
  status_id: props.filters.status_id,
  tenant_id: props.filters.tenant_id,
  per_page: props.filters.per_page || 20,
  sort_by: props.filters.sort_by,   // ← 初期値を必ずセット
  sort_dir: props.filters.sort_dir,    // ← 初期値を必ずセット
})
// 選択削除
const selectedIds = ref([])

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.members.data.map(s => s.id) : []
}

const resetSelectedIds = () => {
  selectedIds.value = []
}

const selectAll = computed({
  get() {
    return selectedIds.value.length === props.members.data.length
  }
})

watch(() => props.members.current_page, () => {
  selectedIds.value = []
})


// persistQueryに各検索項目を追加
const persistQuery = () => ({
  tenant_id: form.tenant_id,
  name: form.name,
  status_id: form.status_id,
  per_page: form.per_page,
  sort_by: form.sort_by,
  sort_dir: form.sort_dir,
  page: props.members.current_page
})

const submitSearch = () => {
  console.log(persistQuery())
  router.get(route('admin.member.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// ページ番号クリック
const goPage = (page) => {
  router.get(route('admin.member.index'), { ...persistQuery(), page }, {
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
const deletemember = (member_id) => {
  if (!confirm(t('confirm_delete'))) return
  router.delete(route('admin.member.destroy', member_id), {
    preserveState: true,
    onSuccess: () => {
      router.get(route('admin.member.index'), { ...persistQuery(), page: props.members.current_page }, { preserveState: true })
    }
  })
}
// 複数削除
const bulkDelete = () => {
  if (!confirm(t('confirm_delete_selected'))) return
  router.post(
    route('members.bulkDelete'),
    { ids: selectedIds.value },
    {
      preserveState: true,
      onSuccess: () => {
        // 削除後に検索条件・ページを保持して再取得
        router.get(route('admin.member.index'), { ...persistQuery(), page: props.members.current_page }, { preserveState: true })
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
const startItem = computed(() => props.members.per_page * (props.members.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.members.per_page * props.members.current_page, props.members.total))

const showProgressModal = ref(false)

const progressForm = reactive({
  member_id: null,
  progress_id: null,
})


const progresses = ref([])

const openProgress = async (member) => {
  // ★ axios より前で判定
  if (member.status.id !== 1) {
    alert('申請中のデータのみ進捗を訂正できます。')
    return
  }

  // ここから先だけ axios
  const res = await axios.get(
    `/admin/member/${member.id}/progress/edit`
  )

  progressForm.member_id = member.id
  progressForm.progress_id = res.data.member.progress_id
  progresses.value = res.data.progresses

  showProgressModal.value = true
}


const submitProgress = async () => {
  await axios.put(
    `/admin/member/${progressForm.member_id}/progress`,
    {
      progress_id: progressForm.progress_id,
    }
  )

  closeModal()

  // 一覧だけ再取得
  router.reload({ only: ['members'] })
}

const showStatusModal = ref(false)

const statusForm = ref({
  member_id: null,
  status_id: null,
})

const statuses = ref([])

const openStatus = async (member) => {
  const res = await axios.get(
    `/admin/member/${member.id}/status/edit`
  )

  statusForm.value.member_id = member.id
  statusForm.value.status_id = res.data.member.status_id
  statuses.value = res.data.statuses

  showStatusModal.value = true
}

const submitStatus = async () => {
  await axios.put(
    `/admin/member/${statusForm.value.member_id}/status`,
    { 
      status_id: statusForm.value.status_id,
    }
  )

  showStatusModal.value = false
  router.reload({ only: ['members'] })
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
  member_id: null,
  type_id: null,
})

const file = ref(null)

const documentTypes = [
  { id: 1, name: '履歴事項全部証明書' },
  { id: 2, name: '郵送先確認書' },
  { id: 3, name: '口座振替依頼書' },
  { id: 4, name: '委任状' },
]

/**
 * モーダルを開く（status / progress と同型）
 */
const openUpload = (member) => {
  uploadForm.value.member_id = member.id
  showUploadModal.value = true
}

const fileInput = ref(null)

/**
 * drag & drop
 */
const handleDrop = (e) => {
  const droppedFiles = e.dataTransfer.files
  if (droppedFiles.length && droppedFiles[0].type === 'application/pdf') {
    file.value = droppedFiles[0]
  } else {
    alert('PDF ファイルを1つだけアップロードしてください')
  }
}

/**
 * file input
 */
const onFileChange = (e) => {
  const selected = e.target.files[0]
  if (selected && selected.type === 'application/pdf') {
    file.value = selected
  } else {
    alert('PDF ファイルを選択してください')
    file.value = null
  }
}

/**
 * submit（status と同型）
 */
const submitUpload = async () => {
  if (!file.value || !uploadForm.value.type_id)
    return alert('書類と種別を選択してください')

  const formData = new FormData()
  formData.append('document', file.value)   // ← controller と一致
  formData.append('type_id', uploadForm.value.type_id)
  console.log(formData);

  try {
    await axios.post(
      `/admin/member/${uploadForm.value.member_id}/upload-document`,
      formData,
      //      { headers: { 'Content-Type': 'multipart/form-data' } }
    )

    alert('アップロード完了')
    showUploadModal.value = false
    router.reload({ only: ['members'] })
  } catch (err) {
    console.error(err)
    alert('アップロード失敗')
  }
}

</script>