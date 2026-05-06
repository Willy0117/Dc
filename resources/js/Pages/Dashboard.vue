<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MyPageNotice from './MyPageNotice.vue'
import { useI18n } from 'vue-i18n'
import RehabApplicationsTable from '@/Components/RehabApplicationsTable.vue'
import { usePage } from '@inertiajs/vue3'

const { t } = useI18n()
const { props } = usePage()

const user = props.user
const applications = props.applications

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>{{ user.name }}</template>
        <MyPageNotice />
    </AppLayout>
</template>