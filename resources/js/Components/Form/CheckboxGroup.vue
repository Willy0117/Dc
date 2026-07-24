<template>
  <div class="space-y-2">
    <Label>{{ label }} <span v-if="required" class="text-destructive">*</span></Label>
    <div class="border rounded-lg p-4 space-y-2.5">
      <label
        v-for="option in options"
        :key="option"
        class="flex items-center gap-2.5 cursor-pointer group"
      >
        <Checkbox
          :model-value="modelValue.includes(option)"
          @update:model-value="toggle(option, $event)"
        />
        <span class="text-sm group-hover:text-foreground text-muted-foreground transition-colors">
          {{ option }}
        </span>
        <Input
          v-if="option === 'その他' && modelValue.includes('その他')"
          v-model="otherText"
          class="h-7 text-sm ml-1 flex-1"
          placeholder="入力してください"
          @click.prevent
        />
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Label }    from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Input }    from '@/components/ui/input'

const props = defineProps({
  label:      { type: String,  required: true },
  options:    { type: Array,   required: true },
  modelValue: { type: Array,   default: () => [] },
  required:   { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])
const otherText = ref('')

const toggle = (option, checked) => {
  const current = [...props.modelValue]
  if (checked) {
    if (!current.includes(option)) current.push(option)
  } else {
    const idx = current.indexOf(option)
    if (idx > -1) current.splice(idx, 1)
  }
  emit('update:modelValue', current)
}
</script>
