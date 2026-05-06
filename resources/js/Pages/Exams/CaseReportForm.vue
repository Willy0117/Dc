<template>
  <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
    {{ t('rehabs.self_report') }} {{ number }}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <InputLabel :value="t('rehabs.facility_name')" />
        <TextInput v-model="form.facility" class="w-full" />
        <InputError :message="form.errors?.facility" />
      </div>
      <div>
        <InputLabel :value="t('rehabs.age')" />
        <TextInput v-model="form.age" type="number" class="w-full" />
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
      <div class="sm:col-span-2">
        <InputLabel :value="t('rehabs.diagnosis')" />
        <TextInput v-model="form.diagnosis" type="text" class="w-full" />
        <InputError :message="form.errors?.age" />
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
        <InputError :message="form.errors?.rehab" />
      </div>

      <div class="sm:col-span-2">
        <InputLabel :value="t('rehabs.rehab_program')" />
        <textarea
          v-model="form.rehab"
          rows="3"
          class="input-field"
        />
        <InputError :message="form.errors?.rehab" />
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
</template>

<script setup>
import { computed } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  modelValue: Object,
  number: Number
})

const emit = defineEmits(['update:modelValue'])

const form = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})
</script>
<style>
.input-field {
  @apply w-full rounded-md border border-gray-300 px-3 py-2 text-sm
         shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500;
}
</style>
