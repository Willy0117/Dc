<template>
  <div class="relative w-full">
    <input
      ref="inputEl"                       
      type="text"
      v-model="inputValue"
      @keydown.down.prevent="highlightNext"
      @keydown.up.prevent="highlightPrev"
      @keydown.enter.prevent="selectHighlighted"
      @focus="onFocus"
      @blur="hideWithDelay"
      class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
      :placeholder="placeholder"
      autocomplete="off"
    />

    <ul
      v-if="showSuggestions && filteredSuggestions.length"
      class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg max-h-48 overflow-y-auto"
    >
      <li
        v-for="(item, index) in filteredSuggestions"
        :key="item.id"
        :class="['px-3 py-2 cursor-pointer', index === highlightedIndex ? 'bg-indigo-100' : '']"
        @mousedown.prevent="selectSuggestion(item)"
      >
        {{ getLabel(item) }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: [String, Object],
    default: '',
  },
  placeholder: {
    type: String,
    default: '会社名を入力してください...',
  },
  labelKey: {
    type: String,
    default: 'company_name',
  },
})

const emit = defineEmits(['update:modelValue'])

const inputEl = ref(null) 
const inputValue = ref('')
const showSuggestions = ref(false)
const filteredSuggestions = ref([])
const highlightedIndex = ref(-1)

// ラベル取得の安全策
const getLabel = (item) => {
  if (!item) return ''
  return typeof item === 'object' ? item[props.labelKey] || '' : item
}

// modelValueが変わったら入力欄にも反映
watch(
  () => props.modelValue,
  (val) => {
    inputValue.value = getLabel(val)
  },
  { immediate: true }
)

const fetchSuggestions = async (keyword) => {
  try {
    const res = await axios.get('/api/customers', { params: { keyword } })
    filteredSuggestions.value = res.data
    highlightedIndex.value = 0
    showSuggestions.value = res.data.length > 0
  } catch {
    filteredSuggestions.value = []
    showSuggestions.value = false
  }
}

// ③ watch を以下のように修正
watch(
  () => inputValue.value,
  (val) => {
    const kw = (val || '').trim()
    // フォーカス中は empty でも候補を取得
    if (!kw && document.activeElement !== inputEl.value) {
      filteredSuggestions.value = []
      showSuggestions.value = false
      return
    }
    // キーワード or フォーカス時は常に取得
    fetchSuggestions(kw)
  }
)


// フォーカス時にも候補を表示
const onFocus = () => {
    fetchSuggestions((inputValue.value || '').trim())
}

// キーボード上下
const highlightNext = () => {
  if (highlightedIndex.value < filteredSuggestions.value.length - 1) {
    highlightedIndex.value++
  }
}
const highlightPrev = () => {
  if (highlightedIndex.value > 0) {
    highlightedIndex.value--
  }
}

// Enterキー or クリックで選択
const selectHighlighted = () => {
  if (
    highlightedIndex.value >= 0 &&
    highlightedIndex.value < filteredSuggestions.value.length
  ) {
    selectSuggestion(filteredSuggestions.value[highlightedIndex.value])
  }
}
const selectSuggestion = (item) => {
  console.log('🔔 [AutoCompleteInput] selectSuggestion:', item)
  emit('update:modelValue', item)
  inputValue.value = getLabel(item)
  showSuggestions.value = false
}

// Blur時の遅延非表示
const hideWithDelay = () => {
  setTimeout(() => {
    showSuggestions.value = false
  }, 150)
}
</script>

<style scoped>
/* お好みで微調整 */
</style>


