<template>
  <div class="space-y-2">
    <Label>{{ label }} <span v-if="required" class="text-destructive">*</span></Label>
    <div class="border rounded-lg p-4 space-y-2.5">
      <label
        v-for="option in options"
        :key="option"
        class="flex items-center gap-2.5 cursor-pointer group"
      >
        <input
          type="radio"
          :value="option"
          :checked="modelValue === option"
          @change="$emit('update:modelValue', option)"
          class="accent-primary w-4 h-4"
        />
        <span class="text-sm group-hover:text-foreground text-muted-foreground transition-colors">
          {{ option }}
        </span>
      </label>
    </div>
  </div>
</template>

<script setup>
import { Label } from '@/components/ui/label'

defineProps({
  label:      { type: String,  required: true },
  options:    { type: Array,   required: true },
  modelValue: { type: String,  default: '' },
  required:   { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>
