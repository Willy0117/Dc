<template>
  <AppLayout>
    <template #header>
      {{ t('instructors.list') }}
    </template>

    <div class="p-6">

      <!-- 🔍 検索 -->
      <form @submit.prevent="doSearch" class="mb-4 flex items-center gap-2">
        <input
          v-model="search"
          type="text"
          placeholder="検索"
          class="border px-3 py-2 rounded w-60"
        />

        <button
          type="submit"
          class="bg-blue-500 text-white px-4 py-2 rounded"
        >
          検索
        </button>
      </form>

      <!-- テーブル -->
      <table class="table-auto w-full border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th class="border px-3 py-2">{{ t('name') }}</th>
            <th class="border px-3 py-2">{{ t('update_period') }}</th>
            <th class="border px-3 py-2">{{ t('total_points') }}</th>
            <th class="border px-3 py-2">{{ t('conference_count') }}</th>
            <th class="border px-3 py-2">{{ t('status') }}</th>
            <th class="border px-3 py-2">{{ t('actions') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="member in members.data"
            :key="member.id"
            class="odd:bg-white even:bg-gray-100"
          >

            <td class="px-3 py-2">
              <input type="checkbox" :value="member.id" v-model="selectedIds" />
            </td>
            <td class="border px-3 py-2">{{ member.name }}</td>

            <!-- 最新更新サイクル -->
            <td class="border px-3 py-2">
              {{ member.update_cycles[0]?.start_date ?? '-' }} ～ {{ member.update_cycles[0]?.end_date ?? '-' }}
            </td>
            <td class="border px-3 py-2">
              {{ member.update_cycles[0]?.total_points ?? '-' }}
            </td>
            <td class="border px-3 py-2">
              {{ member.update_cycles[0]?.conference_count ?? '-' }}
            </td>
            <td class="border px-3 py-2">
              <!-- before_update の場合のみ審査ボタン表示 -->
              <div v-if="member.update_cycles[0]?.status === 'before_update'">
                <button
                  @click="openReviewModal(member)"
                  class="bg-yellow-500 text-white px-2 py-1 rounded text-sm"
                >
                  {{ t('before_update') }}
                </button>
              </div>

              <!-- before_update 以外は通常ステータス表示 -->
              <div v-else :class="statusColor(member.update_cycles[0]?.status)">
                {{ statusLabel(member.update_cycles[0]?.status) }}
              </div>

              <!-- 審査モーダル -->
              <div v-if="reviewModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-lg w-96 p-4">
                  <h2 class="text-lg font-bold mb-2">{{ t('review_member') }}</h2>

                  <div class="mb-4">
                    <label class="block mb-2">{{ t('choose_status') }}</label>
                    <select v-model="reviewModal.status" class="w-full border rounded p-2">
                      <option value="updated">{{ t('update') }}</option>
                      <option value="no_update">{{ t('no_update') }}</option>
                    </select>
                  </div>

                  <div class="mb-4" v-if="reviewModal.status === 'rejected' || reviewModal.status === 'no_update'">
                    <label class="block mb-2">{{ t('reason') }}</label>
                    <textarea
                      v-model="reviewModal.reason"
                      class="w-full border rounded p-2"
                      rows="4"
                      placeholder="理由を入力してください"
                    ></textarea>
                  </div>

                  <div class="flex justify-end space-x-2">
                    <button
                      class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
                      @click="reviewModal.show = false"
                    >
                      {{ t('cancel') }}
                    </button>
                    <button
                      type="submit"
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                      @click="submitReview"
                    >
                      {{ t('submit') }}
                    </button>
                  </div>
                </div>
              </div>
            </td>

            <td class="border px-3 py-2">
              <Link
                :href="route('admin.instructorMembers.show', {
                  member: member.id,
                  search: search,
                  page: page
                })"
                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
              >
                {{ t('details') }}
              </Link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination
        :paginator="members"
        :onPageChange="goPage"
        :startItem="startItem"
        :endItem="endItem"
      />

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// props
const props = defineProps({
  members: Object, // 👈 paginator オブジェクトに変更
  filters: Object, // { search: "" }
})

const members = props.members
const search = ref(props.filters?.search ?? "")
const page = ref(props.filters?.page ?? 1)

// ---------- 検索 ----------
const doSearch = () => {
  page.value = 1
  router.get(route('admin.instructorMembers.index'),  { 
    search: search.value, 
    page: page.value 
  }, {
    preserveState: false,
    preserveScroll: true,
  })
}

// ---------- ページ切替 ----------
const goPage = (p) => {
  page.value = p
  router.get(route('admin.instructorMembers.index'), {
    page: page.value,
    search: search.value
  }, {
    preserveState: false,
    preserveScroll: true
  })
}

// ---------- 件数計算（あなたのロジック） ----------
const startItem = computed(() => {
  if (members.total === 0) return 0
  return members.per_page * (members.current_page - 1) + 1
})

const endItem = computed(() => {
  if (members.total === 0) return 0
  return Math.min(members.per_page * members.current_page, members.total)
})

// statusLabel / statusColor を function で定義
function statusLabel(s) {
  return {
    updated: t('updated'),
    before_update: t('before_update'),
    no_update: t('no_update'),
    rejected: t('rejected')
  }[s] || '-'
}
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

function statusColor(s) {
  return {
    updated: 'text-green-600 font-semibold',
    before_update: 'text-blue-600 font-semibold',
    no_update: 'text-gray-600',
    rejected: 'text-red-600 font-semibold'
  }[s] || 'text-gray-600'
}

const reviewModal = ref({
  show: false,
  member: null,
  status: 'updated',
  reason: '',
})

function openReviewModal(member) {
  const cycle = member.update_cycles[0] // 最新の更新サイクル
  if (!cycle) return

  reviewModal.value.show = true
  reviewModal.value.cycleId = cycle.id
  reviewModal.value.memberName = member.name
  reviewModal.value.status = 'updated'  // デフォルト選択
  reviewModal.value.reason = ''
}

function submitReview() {
  if (!reviewModal.value.cycleId) return

  router.post(
    route('admin.instructorUpdateCycles.review', reviewModal.value.cycleId),
    {
      status: reviewModal.value.status,
      reason: reviewModal.value.reason,
    },
    {
      onSuccess: () => {
        reviewModal.value.show = false
        router.get(
          route('admin.instructorMembers.index'),
          { search: search.value, page: members.current_page },
          { preserveState: true, preserveScroll: true }
        )
      },
      onError: (errors) => {
        console.error(errors)
      }
    }
  )
}
</script>
