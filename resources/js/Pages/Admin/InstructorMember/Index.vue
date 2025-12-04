<template>
  <AppLayout>
    <template #header>
      {{ t('instructor_members') }}
    </template>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { useI18n } from 'vue-i18n'
import { defineProps } from 'vue'

const { t } = useI18n()

const props = defineProps({ members: Array })

const statusLabel = s => ({
  updated: t('updated'),
  before_update: t('before_update'),
  no_update: t('no_update'),
}[s] || '-')

const statusColor = s => ({
  updated: 'text-green-600 font-semibold',
  before_update: 'text-blue-600 font-semibold',
  no_update: 'text-gray-600',
}[s] || 'text-gray-600')
</script>

