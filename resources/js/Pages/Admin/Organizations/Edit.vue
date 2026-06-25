<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import OrganizationForm from '@/Components/OrganizationForm.vue'
import type { OrganizationEditProps, OrganizationFormData } from '@/types'

const props = defineProps<OrganizationEditProps>()

const isEdit = computed(() => !!props.organization?.id)

function handleSubmit(data: OrganizationFormData) {
  isEdit.value
    ? router.put(`/admin/organizations/${props.organization!.id}`, data)
    : router.post('/admin/organizations', data)
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
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </div>
  </AppLayout>
</template>
