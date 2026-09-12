<script setup lang="ts">
import { computed, watch, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import OrganizationForm from '@/Components/OrganizationForm.vue'
import type { OrganizationEditProps, OrganizationFormData } from '@/types'

const props = defineProps<OrganizationEditProps>()

const isEdit = computed(() => !!props.organization?.id)

// バリデーションエラー（Laravel側の withErrors() が自動的にここへ入る）
const errors = computed(() => usePage().props.errors as Record<string, string>)

// 変更点：エラーがあればtoastで警告する。
// 【重要】watchの{immediate:true}はsetup()実行中＝AppLayout（Toaster含む）が
// まだ描画される前に同期的に発火してしまうため使わない。
// onMountedで、画面が実際に描画された後に初回チェックする。
function notifyIfErrors() {
  const val = errors.value
  if (!val) return
  const keys = Object.keys(val)
  if (keys.length === 0) return

  const hasAddressError = keys.some(k =>
    k.startsWith('location_address') ||
    k.startsWith('shipping_address') ||
    k.startsWith('billing_address')
  )
  const hasMemberError = keys.some(k => k.startsWith('members.'))

  toast.error('入力内容にエラーがあります。ご確認ください。', {
    description: hasAddressError
      ? '住所情報タブをご確認ください。'
      : (hasMemberError ? '先生登録タブをご確認ください。' : '入力内容をご確認ください。'),
  })
}

onMounted(() => {
  notifyIfErrors()
})

// ページ遷移せず props だけ更新されるケースのための保険（immediateなし）
watch(() => errors.value, () => {
  notifyIfErrors()
})

function handleSubmit(data: OrganizationFormData) {
  isEdit.value
    ? router.put(`/admin/organizations/${props.organization!.id}`, data, {
        onSuccess: () => router.get(route('admin.organizations.index'), props.filters ?? {})
      })
    : router.post('/admin/organizations', data, {
        onSuccess: () => router.get(route('admin.organizations.index'), props.filters ?? {})
      })
}

function handleCancel() {
  router.get(route('admin.organizations.index'), props.filters ?? {})
}
</script>

<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">契約先管理</p>
      <h1 class="text-xl font-semibold">
        {{ isEdit ? '契約先を編集' : '契約先を登録' }}
      </h1>
    </template>
    <div class="h-full flex flex-col">
      <OrganizationForm
        v-bind="props"
        :errors="errors"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </div>
  </AppLayout>
</template>
