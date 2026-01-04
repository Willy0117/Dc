<template>
  <div class="relative w-full">
    <label class="block text-sm font-medium mb-1">{{ label }}</label>
    <div class="relative flex items-center">
      <input
        type="text"
        v-model="search"
        :placeholder="placeholder"
        class="w-full border rounded px-3 py-2 pr-8"
        @focus="showDropdown = true"
        @blur="hideDropdown"
        @input="onInput"
        @keydown="onKeyDown"
      />
      <button 
        v-if="search"
        @click="clear"
        type="button"
        class="absolute right-2 text-gray-400 hover:text-gray-600 flex items-center justify-center h-full"
      >
        X
      </button>
    </div>

    <ul v-if="showDropdown && options.length"
        class="absolute z-10 w-full bg-white border rounded mt-1 max-h-96 overflow-y-auto">
      <li v-for="(item,index) in options" :key="item.id"
          @mousedown.prevent="select(item)"
          :class="{'bg-blue-200': activeIndex===index}"
          class="px-3 py-2 cursor-pointer hover:bg-gray-200">
        {{ item.label }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  modelValue: [String, Number],
  initial: { type: String, default: null }, 
  label: String,
  placeholder: String,
  fetchUrl: String,
  extraParams: { type: Object, default: () => ({}) }
})

console.log(props)

const emit = defineEmits([
  'update:modelValue',
  'select'
])

const search = ref(props.modelValue ?? '')
const options = ref([])
const showDropdown = ref(false)
const activeIndex = ref(-1)
/*
const onInput = async () => {
  if (!props.fetchUrl) return
  const res = await fetch(`${props.fetchUrl}?q=${encodeURIComponent(search.value)}`)
  options.value = await res.json()
  activeIndex.value = -1
}
*/
const onInput = async () => {
  if (!props.fetchUrl) return

  const params = new URLSearchParams()
  params.append('q', search.value)

  if (props.extraParams?.category !== undefined) {
    params.append('category', props.extraParams.category)
  }
  if (props.extraParams?.bank_code !== undefined) {
    params.append('bank_code', props.extraParams.bank_code)
  }

  const res = await fetch(`${props.fetchUrl}?${params.toString()}`)
  options.value = await res.json()
  activeIndex.value = -1
}

watch(
  () => props.modelValue,
  (val) => {
    if (val?.label) {
      search.value = val.label
    } else if (typeof val === 'string') {
      search.value = val
    } else {
      search.value = ''
    }
  },
  { immediate: true }
)

// 初期値を反映
onMounted(() => {
  if (props.initial) {
    search.value = props.initial
  } else if (props.modelValue) {
    search.value = props.modelValue
  }
})

const select = (item) => {
  emit('update:modelValue', item.id)
  emit('selected', item)
  search.value = item.label
  showDropdown.value = false
  activeIndex.value = -1
}

const clear = () => {
  search.value = ''
  emit('update:modelValue', '')
  options.value = []
  activeIndex.value = -1
}

const hideDropdown = () => setTimeout(() => showDropdown.value = false, 100)

const onKeyDown = (e) => {
  if (!options.value.length) return
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    activeIndex.value = (activeIndex.value + 1) % options.value.length
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    activeIndex.value = (activeIndex.value - 1 + options.value.length) % options.value.length
  } else if (e.key === 'Enter') {
    e.preventDefault()
    if (activeIndex.value >= 0) select(options.value[activeIndex.value])
  }
}

</script>


