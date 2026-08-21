<template>
  <div class="border rounded-lg">
    <div class="p-3 border-b">
      <Input v-model="query" placeholder="名前で絞り込み..." />
    </div>

    <!-- 一括操作バー -->
    <div class="px-3 py-2 border-b bg-muted/50 flex items-center justify-between text-sm">
      <span class="text-muted-foreground">
        絞り込み結果: {{ filteredItems.length }}件({{ filteredSelectedCount }}件選択中)
      </span>
      <div class="flex gap-2">
        <Button type="button" variant="outline" size="sm" @click="selectAllFiltered">
          表示中を全選択
        </Button>
        <Button type="button" variant="outline" size="sm" @click="deselectAllFiltered">
          表示中を全解除
        </Button>
      </div>
    </div>

    <div v-if="selectedItems.length > 0" class="p-3 border-b flex flex-wrap gap-2">
      <Badge v-for="item in selectedItems" :key="item.id" variant="secondary" class="gap-1">
        {{ item.name }}
        <button type="button" @click="toggle(item)" class="hover:text-destructive">
          <X class="w-3 h-3" />
        </button>
      </Badge>
    </div>

    <div class="max-h-64 overflow-y-auto p-3 space-y-1">
      <label
        v-for="item in filteredItems"
        :key="item.id"
        class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-muted cursor-pointer text-sm"
      >
        <Checkbox
          :model-value="isSelected(item.id)"
          @update:model-value="() => toggle(item)"
        />
        {{ item.name }}
      </label>
      <p v-if="filteredItems.length === 0" class="text-sm text-muted-foreground text-center py-4">
        該当する項目がありません
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { Button } from '@/components/ui/button'
import { X } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  items: { type: Array, required: true }, // [{ id, name }, ...]
})
const emit = defineEmits(['update:modelValue'])

const query = ref('')
const selectedIds = ref([...props.modelValue])

const selectedItems = computed(() =>
  props.items.filter(i => selectedIds.value.includes(i.id))
)

const filteredItems = computed(() => {
  if (!query.value) return props.items
  return props.items.filter(i => i.name.includes(query.value))
})

const filteredSelectedCount = computed(() =>
  filteredItems.value.filter(i => selectedIds.value.includes(i.id)).length
)

function isSelected(id) {
  return selectedIds.value.includes(id)
}

function toggle(item) {
  if (selectedIds.value.includes(item.id)) {
    selectedIds.value = selectedIds.value.filter(id => id !== item.id)
  } else {
    selectedIds.value.push(item.id)
  }
  emit('update:modelValue', selectedIds.value)
}

function selectAllFiltered() {
  const filteredIds = filteredItems.value.map(i => i.id)
  const merged = new Set([...selectedIds.value, ...filteredIds])
  selectedIds.value = Array.from(merged)
  emit('update:modelValue', selectedIds.value)
}

function deselectAllFiltered() {
  const filteredIds = new Set(filteredItems.value.map(i => i.id))
  selectedIds.value = selectedIds.value.filter(id => !filteredIds.has(id))
  emit('update:modelValue', selectedIds.value)
}

watch(() => props.modelValue, (val) => { selectedIds.value = [...val] })
</script>