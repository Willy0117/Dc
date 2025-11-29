<template>
  <div>
    <label class="block font-medium">{{ label }}</label>
    <input type="file" accept="application/pdf" @change="onFileChange" />
    <span v-if="fileName">{{ fileName }}</span>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  label: String,
  modelValue: File
})
const emit = defineEmits(['update:modelValue'])

const fileName = ref(props.modelValue ? props.modelValue.name : '')

const onFileChange = (e) => {
  const file = e.target.files[0]
  emit('update:modelValue', file)
  fileName.value = file ? file.name : ''
}
</script>
