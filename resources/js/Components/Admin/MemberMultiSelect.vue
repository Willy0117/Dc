<template>
  <div class="border rounded-lg">
    <div class="p-3 border-b flex gap-2 flex-wrap items-center">
      <Input v-model="query" placeholder="先生名で絞り込み..." class="flex-1 min-w-[200px]" />

      <Select v-model="tierFilter">
        <SelectTrigger class="w-36">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">全グレード</SelectItem>
          <SelectItem v-for="(label, tier) in tierLabels" :key="tier" :value="String(tier)">
            {{ label }}
          </SelectItem>
        </SelectContent>
      </Select>

      <div class="flex gap-2 ml-auto">
        <Button type="button" variant="outline" size="sm" @click="selectAllFiltered">
          表示中を全選択
        </Button>
        <Button type="button" variant="outline" size="sm" @click="deselectAllFiltered">
          表示中を全解除
        </Button>
      </div>
    </div>

    <div class="px-3 py-2 border-b bg-muted/30 text-sm text-muted-foreground">
      {{ selectedIds.length }}件選択中 / 絞り込み結果 {{ filteredItems.length }}件
    </div>

    <div class="max-h-96 overflow-y-auto divide-y">
      <label
        v-for="item in sortedFilteredItems"
        :key="item.id"
        class="flex items-center justify-between gap-3 px-4 py-2.5 cursor-pointer text-sm transition-colors"
        :class="isSelected(item.id) ? 'bg-blue-50 hover:bg-blue-100' : 'hover:bg-muted'"
      >
        <span class="flex items-center gap-3 min-w-0">
          <Checkbox
            :model-value="isSelected(item.id)"
            @update:model-value="() => toggle(item)"
          />
          <span class="truncate">{{ item.name }}</span>
          <span class="text-xs text-muted-foreground shrink-0">{{ item.organization_name }}</span>
        </span>
        <Badge variant="outline" class="text-xs shrink-0">{{ item.tier_label ?? tierLabels[item.tier] ?? '-' }}</Badge>
      </label>
      <p v-if="filteredItems.length === 0" class="text-sm text-muted-foreground text-center py-8">
        該当する先生がいません
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  items: { type: Array, required: true }, // [{ id, name, organization_name, tier, tier_label }, ...]
})
const emit = defineEmits(['update:modelValue'])

// Member.php::TIER_LABELS と同じ値
const tierLabels = { 1: 'ベーシック', 2: 'アドバンス', 3: 'エキスパート', 4: 'マスター' }

const query = ref('')
const tierFilter = ref('all')
const selectedIds = ref([...props.modelValue])

const filteredItems = computed(() => {
  return props.items.filter(i => {
    const matchesQuery = !query.value || i.name.includes(query.value)
    const matchesTier = tierFilter.value === 'all' || String(i.tier) === tierFilter.value
    return matchesQuery && matchesTier
  })
})

const sortedFilteredItems = computed(() => {
  return [...filteredItems.value].sort((a, b) => {
    const aSel = selectedIds.value.includes(a.id) ? 0 : 1
    const bSel = selectedIds.value.includes(b.id) ? 0 : 1
    if (aSel !== bSel) return aSel - bSel
    return a.name.localeCompare(b.name, 'ja')
  })
})

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
