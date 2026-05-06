<template>
  <AppLayout>
    <template #header>{{ t('exams.reports') }}</template>
    <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <InputLabel :value="t('rehabs.is_detailed')" />
        <select v-model="form.is_detailed" class="input-field">
          <option value="0">{{ t('rehabs.regular_case') }}</option>
          <option value="1">{{ t('rehabs.detailed_case_(form5)') }}</option>
        </select>
      </div>

      <div v-if="form.is_detailed == 1">
        <InputLabel :value="t('rehabs.supervisor')" />
        <TextInput v-model="form.supervisor" class="input-field" />
        <InputError :message="form.errors?.supervisor" />
      </div>
      <div>
        <InputLabel :value="t('rehabs.facility_name')" />
        <TextInput v-model="form.facility_name" class="input-field" />
        <InputError :message="form.errors?.facility_name" />
      </div>
      <div>
        <InputLabel :value="t('rehabs.age')" />
        <TextInput v-model="form.age" type="number" class="input-field" />
        <InputError :message="form.errors?.age" />
      </div>
      <div>
        <InputLabel :value="t('rehabs.gender')" />
        <select v-model="form.gender" class="input-field">
          <option value="male">{{ t('rehabs.male') }}</option>
          <option value="female">{{ t('rehabs.female') }}</option>
          <option value="other">{{ t('rehabs.other') }}</option>
        </select>
      </div>
      <div>
        <InputLabel :value="t('rehabs.visit_type')" />
        <select v-model="form.visit_type" class="input-field">
          <option value="outpatient">{{ t('rehabs.outpatient') }}</option>
          <option value="inpatient">{{ t('rehabs.inpatient') }}</option>
        </select>
      </div>
      <div v-if="form.is_detailed == 1" class="sm:col-span-2">
        <InputLabel :value="t('rehabs.body_build')" />
        <textarea
          v-model="form.body_build"
          rows="3"
          class="input-field"
        />
        <InputError :message="form.errors?.body_build" />
      </div>
      <div class="sm:col-span-2">
        <InputLabel :value="t('rehabs.diagnosis')" />
        <TextInput v-model="form.diagnosis" type="text" class="input-field" />
        <InputError :message="form.errors?.diagnosis" />
      </div>
      <div class="sm:col-span-2">
        <InputLabel :value="t('rehabs.current_history')" />
        <textarea
          v-model="form.current_history"
          rows="3"
          class="input-field"
        />
        <InputError :message="form.errors?.current_history" />
      </div>
      <div class="sm:col-span-2">
        <InputLabel :value="t('rehabs.past_history')" />
        <textarea
          v-model="form.past_history"
          rows="3"
          class="input-field"
        />
        <InputError :message="form.errors?.past_history" />
      </div>

      <div v-if="form.is_detailed == 1" class="sm:col-span-2">
        <InputLabel :value="t('rehabs.findings_assessment')" />
        <textarea
          v-model="form.findings_assessment"
          rows="3"
          class="input-field"
        />
        <InputError :message="form.errors?.findings_assessment" />
      </div>

      <div class="sm:col-span-2">
        <InputLabel :value="t('rehabs.rehab_program')" />
        <textarea
          v-model="form.rehab_program"
          rows="3"
          class="input-field"
        />
        <InputError :message="form.errors?.rehab_program" />
      </div>
      <div class="sm:col-span-2">
        <InputLabel :value="t('rehabs.future_plan')" />
        <textarea
          v-model="form.future_plan"
          rows="3"
          class="input-field"
        />
        <InputError :message="form.errors?.future_plan" />
      </div>
    </div>
  </div>
  <div class="flex justify-end mt-6">
    <PrimaryButton @click="submit">
      {{ form.id ? t('rehabs.update') : t('rehabs.create') }}
    </PrimaryButton>
  </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage,useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useI18n } from 'vue-i18n'


const { t } = useI18n()

const props = defineProps({
  report: Object,
})

const form = useForm({
    id:props.report?.id ?? '',
    is_detailed:props.report?.is_detailed ?? 0,
    supervisor: props.report?.supervisor ?? '',
    facility_name: props.report?.facility_name ?? '',
    visit_type: props.report?.visit_type ?? 'inpatient',
    age: props.report?.age ?? '',
    gender: props.report?.gender ?? 'male',
    body_build: props.report?.body_build ?? '',
    diagnosis: props.report?.diagnosis ?? '',
    current_history: props.report?.current_history ?? '',
    past_history: props.report?.past_history ?? '',
    rehab_program: props.report?.rehab_program ?? '',
    future_plan: props.report?.future_plan ?? '',
    findings_assessment: props.report?.findings_assessment ?? '',
})

const submit = () => {
  console.log(form)
  if (form.id) {
    form.put(route('reports.update', form.id))
  } else {
    form.post(route('reports.store'))
  }
}
</script>
<style>
.input-field {
  @apply w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm;
  @apply focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500;
  @apply bg-white;
  appearance: none;
}
</style>
