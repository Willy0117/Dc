<script setup lang="ts">
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import MemberForm from '@/Components/MemberForm.vue'
import type { MemberEditProps, MemberFormData } from '@/types'

const props = defineProps<MemberEditProps>()

const isEdit = computed(() => !!props.member?.id)

// バリデーションエラー（Laravel側の withErrors() が自動的にここへ入る）
const errors = computed(() => usePage().props.errors as Record<string, string>)

function handleSubmit(data: MemberFormData) {
  isEdit.value
    ? router.put(`/admin/members/${props.member!.id}`, data)
    : router.post('/admin/members', data)
}

function handleCancel() {
  router.visit('/admin/members', { data: props.filters })
}
</script>

<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">会員管理</p>
      <h1 class="text-xl font-semibold">
        {{ isEdit ? '会員情報を編集' : '新規会員登録' }}
      </h1>
    </template>
    <div class="h-full flex flex-col">
      <MemberForm
        v-bind="props"
        :errors="errors"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </div>
  </AppLayout>
</template>