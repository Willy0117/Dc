<template>
  <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
    <h3 class="text-md font-medium text-gray-900">{{ t('self_report') }}</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label>{{ t('facility_name') }}</label>
        <input v-model="form.facility" type="text" class="input-field" />
      </div>
      <div>
        <label>{{ t('age') }}</label>
        <input v-model="form.age" type="number" class="input-field" />
      </div>
      <div>
        <label>{{ t('gender') }}</label>
        <select v-model="form.gender" class="input-field">
          <option value="male">{{ t('male') }}</option>
          <option value="female">{{ t('female') }}</option>
          <option value="other">{{ t('other') }}</option>
        </select>
      </div>
      <div>
        <label>{{ t('visit_type') }}</label>
        <select v-model="form.visit_type" class="input-field">
          <option value="outpatient">{{ t('outpatient') }}</option>
          <option value="inpatient">{{ t('inpatient') }}</option>
        </select>
      </div>
      <div class="sm:col-span-2">
        <label>{{ t('diagnosis') }}</label>
        <input v-model="form.diagnosis" type="text" class="input-field" />
      </div>
      <div class="sm:col-span-2">
        <label>{{ t('current_history') }}</label>
        <textarea v-model="form.current_history" rows="3" class="input-field"></textarea>
      </div>
      <div class="sm:col-span-2">
        <label>{{ t('past_history') }}</label>
        <textarea v-model="form.past_history" rows="2" class="input-field"></textarea>
      </div>
      <div class="sm:col-span-2">
        <label>{{ t('rehab_program') }}</label>
        <textarea v-model="form.rehab" rows="3" class="input-field"></textarea>
      </div>
      <div class="sm:col-span-2">
        <label>{{ t('future_plan') }}</label>
        <textarea v-model="form.future_plan" rows="2" class="input-field"></textarea>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, toRefs, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  modelValue: Object
})
const emit = defineEmits(['update:modelValue'])

const form = reactive({ ...props.modelValue })

watch(form, () => emit('update:modelValue', form), { deep: true })
</script>

<style scoped>
.input-field {
  margin-top: 0.25rem;
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  padding: 0.5rem;
}
</style>
