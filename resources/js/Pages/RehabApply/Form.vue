<template>
    <AppLayout title="form.self_report">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ self_report }}
            </h2>
        </template>

        <div class="py-12">
            <SelfReportSection v-model="form.self_report" />

            <div class="flex justify-end">
                <button
                    @click="saveSelfReport"
                    class="btn-primary"
                >
                    {{ t('save') }}
                </button>
            </div>
        </div>
  </Applayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SelfReportSection from './Components/SelfReportSection.vue'
import { Inertia } from '@inertiajs/inertia'

// Inertia props を取得
const page = usePage()
const user = computed(() => page.props.user)

const { t } = useI18n()

const form = reactive({
  self_report: {
    facility: '',
    age: '',
    gender: 'male',
    visit_type: 'outpatient',
    diagnosis: '',
    current_history: '',
    past_history: '',
    rehab: '',
    future_plan: ''
  }
})

const saveSelfReport = () => {
  Inertia.post(route('rehab.store'), form.self_report)
}
</script>

