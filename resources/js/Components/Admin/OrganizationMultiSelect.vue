<template>
  <div class="border rounded-lg">
    <div class="p-3 border-b flex gap-2 flex-wrap items-center">
      <Input v-model="query" placeholder="病院名で絞り込み..." class="flex-1 min-w-[200px]" />

      <Select v-model="tierFilter">
        <SelectTrigger class="w-36">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">全Tier</SelectItem>
          <SelectItem v-for="(label, tier) in tierLabels" :key="tier" :value="String(tier)">
            {{ label }}
          </SelectItem>
        </SelectContent>
      </Select>

      <Select v-model="prefFilter">
        <SelectTrigger class="w-36">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">全都道府県</SelectItem>
          <SelectItem v-for="pref in prefectures" :key="pref" :value="pref">{{ pref }}</SelectItem>
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
          <span class="text-xs text-muted-foreground shrink-0">{{ item.prefecture }}</span>
        </span>
        <Badge variant="outline" class="text-xs shrink-0">{{ tierLabels[item.tier] ?? '-' }}</Badge>
      </label>
      <p v-if="filteredItems.length === 0" class="text-sm text-muted-foreground text-center py-8">
        該当する病院がありません
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
  items: { type: Array, required: true },
})
const emit = defineEmits(['update:modelValue'])

const tierLabels = { 1: 'ブロンズ', 2: 'シルバー', 3: 'ゴールド', 4: 'プラチナ' }

const prefectures = [
  '北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県',
  '茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県',
  '新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県',
  '静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県',
  '奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県',
  '徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県',
  '熊本県','大分県','宮崎県','鹿児島県','沖縄県',
]

const query = ref('')
const tierFilter = ref('all')
const prefFilter = ref('all')
const selectedIds = ref([...props.modelValue])

const filteredItems = computed(() => {
  return props.items.filter(i => {
    const matchesQuery = !query.value || i.name.includes(query.value)
    const matchesTier = tierFilter.value === 'all' || String(i.tier) === tierFilter.value
    const matchesPref = prefFilter.value === 'all' || i.prefecture === prefFilter.value
    return matchesQuery && matchesTier && matchesPref
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