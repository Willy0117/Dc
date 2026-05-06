<template>
  <AppLayout :title="$t('pdf_upload')">
    <template #header>{{ $t('pdf_upload') }}</template>

    <div class="mx-auto py-6 space-y-6">

      <div class="p-4 border rounded">
        <form @submit.prevent="submit" class="space-y-4">
          <div class="col-span-4 bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500 mb-2">
              {{ t('instructors.point') }}
            </p>

            <div class="grid grid-cols-3 gap-4 text-sm">
              <div class="flex flex-col">
                <span class="text-gray-400">{{ t('instructors.approved') }}</span>
                <span class="text-lg font-semibold text-green-600">
                  {{ props?.approvedTotal }}
                </span>
              </div>

              <div class="flex flex-col">
                <span class="text-gray-400">{{ t('instructors.pending') }}</span>
                <span class="text-lg font-semibold text-yellow-500">
                  {{ props?.pendingTotal }}
                </span>
              </div>

              <div class="flex flex-col">
                <span class="text-gray-400">{{ t('instructors.total') }}</span>
                <span class="text-lg font-semibold text-gray-800">
                  {{ props?.total }}
                </span>
              </div>
            </div>
          </div>
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
                <InputLabel :value="t('members.code')" />
                <TextInput v-model="form.instructor_code" type="text" class="input-field" />
                <InputError :message="form.errors?.instructor_code" />
              </div>

              <div>
                <InputLabel :value="t('exams.email')" /> 
                <TextInput v-model="form.email" type="email" class="input-field" />
                <InputError :message="form.errors?.email" />
              </div>

            </div>
          </div>   
          <div class="flex justify-between items-center mt-4">
            <SecondaryButton>
              {{ t('instructors.cancel') }}
            </SecondaryButton>    
            <PrimaryButton v-if="props.isFeeOk">
              {{ t('instructors.send') }}
            </PrimaryButton>    
          </div>
        </form>
      </div>

      <!-- アップロード一覧 -->
      <div>
        <h2 class="text-lg font-semibold mb-2">{{ $t('uploaded_files') }}</h2>

        <div v-if="props.uploads.length === 0" class="text-gray-500">{{ $t('no_uploads') }}</div>

        <div v-else class="grid grid-cols-5 gap-4">
          <div v-for="upload in props.uploads" :key="upload.id" class="border rounded p-2">
            <div class="text-sm font-medium">{{ upload.credit_conference_name }}</div>
            <div class="text-xs text-gray-500"><p>{{ t('instructors.point') }}:{{ upload.points }}</p></div>
            <div class="text-xs text-gray-500">{{ upload.role_name }} - {{ upload.category_name }}</div>
            
            <img
              v-if="upload.thumbnail_path"
              :src="`/pdf-uploads/${upload.id}/thumbnail`"
              alt="PDF Thumbnail"
              class="w-full h-32 object-contain my-2"
            />
            <div v-else class="w-full h-32 bg-gray-100 flex items-center justify-center my-2 text-gray-400 text-xs">
              {{ $t('no_thumbnail') }}
            </div>

            <div class="flex justify-between items-center mt-1">
              <button
                @click="previewPdf = `/pdf-uploads/${upload.id}/view`"
                class="text-blue-600 hover:underline text-xs"
              >
                {{ $t('view_pdf') }}
              </button>
              <!--a
                :href="`/pdf-uploads/${upload.id}/view`"
                target="_blank"
                class="text-blue-600 hover:underline text-xs"
              >
                {{ $t('view_pdf') }}
            </a -->

              <span
                class="text-xs px-1 rounded"
                :class="{
                  'bg-yellow-200 text-yellow-800': upload.status==='pending',
                  'bg-green-200 text-green-800': upload.status==='approved',
                  'bg-red-200 text-red-800': upload.status==='rejected'
                }"
              >
                {{ $t(upload.status) }}
              </span>
            </div>

            <div v-if="upload.status==='rejected'" class="text-xs text-red-600 mt-1">
              {{ upload.rejection_message }}
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import DialogModal from '@/Components/DialogModal.vue';
import { useForm, Link, router, usePage } from '@inertiajs/vue3'

import { ref, computed , watch } from 'vue'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const page = usePage()

const props = defineProps({
  uploads: { type: Array, required: true },
  approvedTotal: Number,
  pendingTotal: Number,
  total: Number,
  totalFee: Number,
  totalPaid: Number,
  isFeeOk: Boolean,
})

const form = useForm({
  last_name: props.member?.last_name ?? '',
  first_name: props.member?.first_name ?? '',
  email: props.member?.email ?? '',
  code: props.member?.code ?? '',
  instructor_code: props.member?.instructor_code ?? '',
})


const previewPdf = ref(null)

const openPdf = (pdfPath) => {
  console.log('PDF PATH:', pdfPath)
  if (!pdfPath) return

  // 例：フルパス化
  previewPdf.value = pdfPath

  // 例：ここで loading true
}

</script>
<style>
.input-field {
  @apply w-full rounded-md border border-gray-300 px-3 py-2 text-sm
         shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500;
}
</style>

