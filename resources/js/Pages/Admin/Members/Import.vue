<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { ref, watch } from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const page = usePage()

const form = useForm({
  file: null,
})

const isDragging = ref(false)

const handleDrop = (e) => {
  e.preventDefault()
  isDragging.value = false

  const file = e.dataTransfer.files[0]
  if (file) {
    form.file = file
  }
}

const handleDragOver = (e) => {
  e.preventDefault()
  isDragging.value = true
}

const handleDragLeave = () => {
  isDragging.value = false
}

const handleFileChange = (e) => {
  form.file = e.target.files[0]
}

const submit = () => {
  form.post(route('admin.members.import.store'), {
    forceFormData: true,
  })
}
</script>

<template>
  <AppLayout>
    <template #header>{{ t('会員インポート') }}</template>

    <div class="p-6 w-full max-w-5xl mx-auto">
        <!-- ドロップエリア -->
        <div
        @drop="handleDrop"
        @dragover="handleDragOver"
        @dragleave="handleDragLeave"
        class="border-2 border-dashed border-gray-300 bg-[#ddd5bc] text-center cursor-pointer transition min-h-[160px] flex items-center justify-center"
        :class="isDragging ? 'bg-blue-100 border-blue-400' : 'border-gray-300'"
        @click="$refs.fileInput.click()"
        >
        <p class="text-gray-600">
            ファイルをドラッグ＆ドロップ または クリックして選択
        </p>

        <input
            type="file"
            ref="fileInput"
            class="hidden"
            @change="handleFileChange"
        />
        </div>

        <!-- ファイル名 -->
        <div v-if="form.file" class="mt-3 text-sm text-gray-700">
        選択ファイル：{{ form.file.name }}
        </div>

        <!-- エラー -->
        <div v-if="form.errors.file" class="text-red-500 mt-2">
        {{ form.errors.file }}
        </div>

        <!-- ボタン -->
        <PrimaryButton
        @click="submit"
        :disabled="form.processing || !form.file"
        class="mt-4"
        >
        インポート
        </PrimaryButton>
    </div>
  </AppLayout>
</template>