<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">先生管理</p>
      <h1 class="text-xl font-semibold">氏名一致候補の確認</h1>
    </template>

    <div class="p-6 space-y-4">

      <p class="text-sm text-muted-foreground">
        氏名が完全一致する先生の組み合わせが見つかると、ここに一覧表示されます。
        同一人物かどうかをご確認の上、「統合」または「別人」を選択してください。
      </p>

      <div class="border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted border-b">
            <tr>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">先生①</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">所属①</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">先生②</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">所属②</th>
              <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground">検知日時</th>
              <th class="px-3 py-2.5 text-center text-xs font-semibold text-muted-foreground w-48">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="candidates.data.length === 0">
              <td colspan="6" class="px-3 py-12 text-center text-muted-foreground">
                確認待ちの候補はありません
              </td>
            </tr>
            <tr
              v-for="c in candidates.data"
              :key="c.id"
              class="odd:bg-white even:bg-muted/30 hover:bg-muted/50 border-b transition-colors"
            >
              <td class="px-3 py-2.5 font-medium">
                {{ c.member?.last_name }} {{ c.member?.first_name }}
              </td>
              <td class="px-3 py-2.5 text-muted-foreground">
                {{ c.member?.organization?.name ?? '-' }}
              </td>
              <td class="px-3 py-2.5 font-medium">
                {{ c.matched_member?.last_name }} {{ c.matched_member?.first_name }}
              </td>
              <td class="px-3 py-2.5 text-muted-foreground">
                {{ c.matched_member?.organization?.name ?? '-' }}
              </td>
              <td class="px-3 py-2.5 text-xs text-muted-foreground">
                {{ dayjs(c.created_at).format('YYYY/MM/DD HH:mm') }}
              </td>
              <td class="px-3 py-2.5">
                <div class="flex items-center justify-center gap-2">
                  <Button size="sm" @click="confirm(c)">
                    <UserCheck class="w-3.5 h-3.5 mr-1" />同一人物として統合
                  </Button>
                  <Button size="sm" variant="outline" @click="reject(c)">
                    <UserX class="w-3.5 h-3.5 mr-1" />別人
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :paginator="candidates" :onPageChange="goPage" />
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { UserCheck, UserX } from 'lucide-vue-next'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Button } from '@/components/ui/button'

const props = defineProps({
  candidates: Object,
})

const confirm = (candidate) => {
  if (!window.confirm(
    `${candidate.member?.last_name}${candidate.member?.first_name} と ${candidate.matched_member?.last_name}${candidate.matched_member?.first_name} を同一人物として統合しますか？`
  )) return

  router.post(route('admin.member-name-matches.confirm', candidate.id), {}, {
    preserveScroll: true,
  })
}

const reject = (candidate) => {
  router.post(route('admin.member-name-matches.reject', candidate.id), {}, {
    preserveScroll: true,
  })
}

const goPage = (page) => {
  router.get(route('admin.member-name-matches.index'), { page }, { preserveState: true })
}
</script>
