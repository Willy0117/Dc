<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- 指導士申込状況セクション -->
                    <section id="instructor-applications" class="bg-white shadow sm:rounded-lg p-6 mb-4">
                    <h2 class="text-lg font-semibold mb-4">{{ t('instructor_application_status') }}</h2>

                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
