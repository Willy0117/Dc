<template>
  <AppLayout :title="$t('pdf_upload')">
    <template #header>{{ $t('pdf_upload') }}</template>

    <div class="max-w-3xl mx-auto py-6 space-y-6">

      <!-- PDFアップロードフォーム -->
      <div class="p-4 border rounded bg-gray-50">
        <h2 class="text-lg font-semibold mb-2">{{ $t('upload_pdf') }}</h2>

        <form @submit.prevent="submit" class="space-y-4">

          <!-- ファイルドラッグ&ドロップ -->
          <div
            class="border-2 border-dashed border-gray-300 p-4 rounded cursor-pointer text-center"
            @dragover.prevent
            @drop.prevent="onFileDrop"
          >
            <p>{{ $t('drag_drop_pdf') }}</p>
            <input type="file" @change="onFileChange" class="hidden" ref="fileInput" />
            <button type="button" class="btn-secondary mt-2" @click="$refs.fileInput.click()">
              {{ $t('select_file') }}
            </button>
            <p v-if="form.file">{{ form.file.name }}</p>
          </div>

          <!-- カテゴリ -->
          <div>
            <label class="block mb-1">{{ $t('category') }}</label>
            <select v-model.number="form.credit_category_id" required class="input w-full">
              <option value="" disabled>{{ $t('select_category') }}</option>
              <option v-for="c in props.creditCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- conference -->
          <div v-if="filteredConferences.length > 0">
            <label class="block mb-1">{{ $t('conference') }}</label>
            <select v-model.number="form.credit_conference_id" required class="input w-full">
              <option value="" disabled>{{ $t('select_conference') }}</option>
              <option v-for="conf in filteredConferences" :key="conf.id" :value="conf.id">{{ conf.name }}</option>
            </select>
          </div>

          <!-- 学術集会のみ session -->
          <div v-if="selectedCategoryIsAcademic">
            <label class="block mb-1">{{ $t('session') }}</label>
            <input v-model="form.session" type="text" class="input w-full" placeholder="第14回" />
          </div>

          <!-- role -->
          <div v-if="filteredRoles.length > 0">
            <label class="block mb-1">{{ $t('role') }}</label>
            <select v-model="form.role_id" required class="input w-full">
              <option value="" disabled>{{ $t('select_role') }}</option>
              <option v-for="r in filteredRoles" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>
          </div>

          <!-- ポイント表示 -->
          <div v-if="selectedRole">
            <label class="block mb-1">{{ $t('points') }}</label>
            <input type="text" class="input w-full" :value="selectedRole.points" readonly />
          </div>

          <div class="flex justify-end">
            <button type="submit" class="btn-primary">{{ $t('upload') }}</button>
          </div>

        </form>
      </div>

      <!-- アップロード一覧 -->
      <div>
        <h2 class="text-lg font-semibold mb-2">{{ $t('uploaded_files') }}</h2>

        <div v-if="props.uploads.length === 0" class="text-gray-500">{{ $t('no_uploads') }}</div>

        <div v-else class="grid grid-cols-3 gap-4">
          <div v-for="upload in props.uploads" :key="upload.id" class="border rounded p-2">
            <div class="text-sm font-medium">{{ upload.credit_conference_name }}</div>
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
              <a
                :href="`/pdf-uploads/${upload.id}/view`"
                target="_blank"
                class="text-blue-600 hover:underline text-xs"
              >
                {{ $t('view_pdf') }}
              </a>

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
import { useForm } from '@inertiajs/vue3'
import { ref, computed , watch } from 'vue'

const props = defineProps({
  uploads: { type: Array, required: true },
  creditCategories: { type: Array, required: true },
  conferences: { type: Array, required: true },
  roles: { type: Array, required: true }
})

const form = useForm({
  file: null,
  credit_category_id: '',
  credit_conference_id: '',
  role_id: '',
  session: ''
})

const fileInput = ref(null)

const filteredConferences = computed(() =>
  props.conferences.filter(c => c.credit_category_id == form.credit_category_id)
)

const filteredRoles = computed(() =>
  props.roles.filter(r =>
    r.credit_category_id == form.credit_category_id &&
    r.credit_conference_id == form.credit_conference_id
  )
);

const selectedRole = computed(() =>
  filteredRoles.value.find(r => r.id == form.role_id) || null
)

const selectedCategoryIsAcademic = computed(() => {
  const cat = props.creditCategories.find(c => c.id == form.credit_category_id)
  return cat ? cat.name === '学術集会' : false
})

function onFileChange(e) {
  form.file = e.target.files[0]
}

function onFileDrop(e) {
  const files = e.dataTransfer.files
  if (files.length > 0) form.file = files[0]
}
watch(() => form.credit_category_id, (v) => {
  console.log("選択中 category_id:", v, typeof v);
});
watch(() => form.credit_conference_id, (v) => {
  console.log("選択中 conference_id:", v, typeof v);
});

function submit() {
  console.log(form)
  form.post('/pdf-uploads', {
    file: form.file,
    credit_category_id: form.credit_category_id,
    credit_conference_id: form.credit_conference_id,
    role_id: form.role_id, 
    session: form.session
  })
}

</script>


