<template>
  <div class="sm:col-span-6">
    <label :for="name" class="block text-sm font-medium text-gray-700">{{ t(label) }}</label>
        <!-- ドロップエリア -->
    <div
      class="mt-1 flex items-center justify-center w-full h-32 border-2 border-dashed rounded-md cursor-pointer bg-[#ddd5bc]"
      :class="isDragging ? 'border-blue-400 bg-blue-50' : 'border-gray-300'"
      @dragover.prevent="isDragging = true"
      @dragleave="isDragging = false"
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
    >
      <div class="text-sm text-gray-500 flex flex-col items-center">
        <div>
        {{ t('drag_and_drop') }} / {{ t('click_to_select') }}
        </div>
        <div v-if="fileName" class="mt-2">
        {{ t('selected_file') }}: {{ fileName }}
        </div>
      </div>  
    </div>
    <!-- hidden file input -->
    <input
      ref="fileInput"
      :id="name"
      type="file"
      class="hidden"
      @change="handleFileChange"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  label: { type: String, required: true },
  name: { type: String, required: true },
  modelValue: File //
})

const emit = defineEmits(['update:modelValue'])

const fileName = ref('')
const isDragging = ref(false)
const fileInput = ref(null)

const handleFileChange = (event) => {
  const file = event.target.files[0]
  fileName.value = file ? file.name : ''
  emit('update:modelValue', file)
}

// ドロップ
const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file) {
    fileName.value = file.name

    // inputにも反映（重要）
    const dt = new DataTransfer()
    dt.items.add(file)
    fileInput.value.files = dt.files
    emit('update:modelValue', file)
  }
}
// クリックでinput開く
const triggerFileInput = () => {
  fileInput.value.click()
}

</script>
