<template>
  <AppLayout>
    <template #header>{{ t('exams.application') }}</template>

    <form class="space-y-6">
      <!-- 申請者情報 -->
      <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <InputLabel :value="t('members.code')" />
            <TextInput v-model="form.code" type="text" class="input-field" />
            <InputError :message="form.errors?.code" />
          </div>
          <div class="sm:col-span-2">
            <InputLabel :value="t('name')" />
            <div class="grid grid-cols-2 gap-2 mt-1">
              <div>
                <TextInput v-model="form.last_name" class="input-field" />
                <InputError :message="form.errors?.last_name" />
              </div>
              <div>
                <TextInput v-model="form.first_name" class="input-field" />
                <InputError :message="form.errors?.first_name" />
              </div>
            </div>
          </div>

          <div>
            <InputLabel :value="t('exams.birthdate')" />
            <TextInput v-model="form.birthdate" placeholder="1986/01/01" class="input-field" />
            <InputError :message="form.errors?.birthdate" />

          </div>
          <div>
            <InputLabel :value="t('exams.gender')" /> 
            <select v-model="form.gender" class="input-field">
              <option value="male">{{ t('rehabs.male') }}</option>
              <option value="female">{{ t('rehabs.female') }}</option>
              <option value="other">{{ t('rehabs.other') }}</option>
            </select>
          </div>

          <div>
            <InputLabel :value="t('exams.occupation')" /> 
            <TextInput v-model="form.occupation" type="text" class="input-field" />
            <InputError :message="form.errors?.occupation" />
          </div>

          <div>
            <InputLabel :value="t('exams.workplace')" /> 
            <TextInput v-model="form.workplace" type="text" class="input-field" />
            <InputError :message="form.errors?.workplace" />
          </div>
          <!-- 所属部科名 -->
          <div>
            <InputLabel :value="t('exams.department')" /> 
            <TextInput v-model="form.department" type="text" class="input-field" />
            <InputError :message="form.errors?.department" />
          </div>

          <!-- 役職名 -->
          <div>
            <InputLabel :value="t('exams.position')" /> 
            <TextInput v-model="form.position" type="text" class="input-field" />
            <InputError :message="form.errors?.position" />
          </div>

          <!-- 勤務先住所 -->
          <div>
            <InputLabel :value="t('exams.address')" /> 
            <TextInput v-model="form.address" type="text" class="input-field" />
            <InputError :message="form.errors?.address" />
          </div>

          <div>
            <InputLabel :value="t('exams.email')" /> 
            <TextInput v-model="form.email" type="email" class="input-field" />
            <InputError :message="form.errors?.email" />
          </div>

          <div>
            <InputLabel :value="t('exams.phone')" /> 
            <TextInput v-model="form.phone" type="text" class="input-field" />
            <InputError :message="form.errors?.phone" />
          </div>
        </div>
      </div>

      <!-- 添付ファイル -->
      <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
        <h2 class="text-lg font-medium text-gray-900">{{ t('exams.attachments') }}</h2>
        <ExamFilesUpload :label="t('exams.certificate')" name="certificate" v-model="form.certificate" />
        <ExamFilesUpload :label="t('exams.recommendation') + '1'" name="recome1" v-model="form.recome1"/>
        <ExamFilesUpload :label="t('exams.recommendation') + '2'" name="recome2" v-model="form.recome2" />
      </div>

      <!-- 送信ボタン（モック） -->
      <div class="flex justify-end">
        <PrimaryButton type="button" @click="submit">
          {{ t('exams.send') }}
        </PrimaryButton>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Layouts/AppLayout.vue'
import ExamFilesUpload from './ExamFilesUpload.vue'
import CaseReportForm from './CaseReportForm.vue'
import { usePage, useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const { t } = useI18n()

const props = defineProps({
  member: Object,
  uploads: { type: Array, required: true },
  creditCategories: { type: Array, required: true },
  conferences: { type: Array, required: true },
  roles: { type: Array, required: true }
})

console.log(props.member)

const form = useForm({
  last_name: props.member?.last_name ?? '',
  first_name: props.member?.first_name ?? '',
  birthdate: props.member?.birthdate ?? '',
  gender: props.member?.gender ?? 'male',
  occupation: '',
  workplace: '',
  department: '',
  position: props.member?.position ?? '',
  address: props.member?.full_address ?? '',
  email: props.member?.email ?? '',
  phone: props.member?.tel ?? '',
  code: props.member?.code ?? '',
  certificate: File,
  recome1: File,
  recome2: File,
})

const submit = () => {
  form.post(route('exams.store'))
}

</script>
<style>
.input-field {
  @apply w-full rounded-md border border-gray-300 px-3 py-2 text-sm
         shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500;
}
</style>









