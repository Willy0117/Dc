<template>
  <div class="max-w-xl mx-auto p-6 space-y-6">
    <div class="flex gap-2 mb-3">
        <button
            v-for="c in bankCategories"
            :key="c.id"
            type="button"
            @click="selectCategory(c)"
            :class="[
            'px-3 py-1 rounded border text-sm',
            selectedCategory === c.value
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-white text-gray-700 hover:bg-gray-100'
            ]"
        >
            {{ c.label }}
        </button>
    </div>
<!--
    <div class="flex gap-2 mb-3">
        <button
        v-for="category in bankCategories"
        :key="category.value"
        @click="selectCategory(category)"
        class="px-3 py-1 border rounded"
        >
        {{ category.label }}
        </button>
    </div>
-->
    <!-- 銀行 -->
    <div>
        <Autocomplete
        v-model="selectedBank"
        label="銀行名"
        fetch-url="/api/banks"
        :extra-params="fetchParams"
        @selected="onBankSelected"
        />
        
        <Autocomplete
        v-if="form.bank_code"
        v-model="selectedBranch"
        label="支店名"
        placeholder="ほん / えき / 中央"
        fetch-url="/api/branches"
        :extra-params="extraParams"
        @selected="onBranchSelected"
        />  
    </div>
    <input type="hidden" v-model="form.bank_code" />
    <input type="hidden" v-model="form.branch_code" />
    <!-- 確認 -->
    <pre class="bg-gray-100 p-3">
銀行: {{ selectedBank }}
支店: {{ selectedBranch }}
    </pre>

  </div>
</template>

<script setup>
import Autocomplete from '@/Components/Autocomplete.vue'    
import { ref, onMounted, watch, nextTick, computed } from 'vue'
import axios from 'axios'
import { Link, router, useForm,usePage } from '@inertiajs/vue3'

const page = usePage()
const bankInput = ref(null)

console.log(page.props) // ← ここで form が見える

const form = useForm({
  bank_name: page.props.form?.bank_name ?? '',
  bank_code: page.props.form?.bank_code ?? '',

  branch_name: page.props.form?.branch_name ?? '',
  branch_code: page.props.form?.branch_code ?? '',  
})

const bankCategories = ref([])
const selectedCategory = ref(null)

/* 銀行 */
const bankKeyword = ref('')
const banks = ref([])
const activeBankIndex = ref(-1)
const selectedBank = ref(null)

/* 支店 */
const branchKeyword = ref('')
const branches = ref([])
const activeBranchIndex = ref(-1)
const selectedBranch = ref(null)

onMounted(async () => {
  const res = await axios.get('/api/bank-categories')

  bankCategories.value = res.data.map(c => ({
    value: c.id,
    label: c.bank_name
  }))
})

/* 銀行検索 */
const searchBanks = async () => {
  if (bankKeyword.value.length < 1) {
    banks.value = []
    return
  }
  const res = await axios.get('/api/banks', {
    params: { 
        keyword: bankKeyword.value,
        category: selectedCategory.value
     }
  })

  banks.value = res.data
  activeBankIndex.value = -1
}

/* 銀行選択 */
const selectBank = (bank) => {
  selectedBank.value = bank
  bankKeyword.value = bank.name
  banks.value = []
  activeBankIndex.value = -1

  form.bank_name = bank.name
  form.bank_code = bank.bank_code
  // 支店リセット
  branchKeyword.value = ''
  branches.value = []
  selectedBranch.value = null
  nextTick(() => {
    bankInput.value?.focus()
  })
}

/* 支店検索 */
const searchBranches = async () => {
  if (!selectedBank.value || branchKeyword.value.length < 1) {
    branches.value = []
    return
  }
  const res = await axios.get('/api/branches', {
    params: {
      bank_code: selectedBank.value.bank_code,
      keyword: branchKeyword.value
    }
  })

  branches.value = res.data
  activeBranchIndex.value = -1
}

/* 支店選択 */
const selectBranch = (branch) => {
  form.branch_name = branch.name
  form.branch_code = branch.branch_code

  selectedBranch.value = branch
  branchKeyword.value = branch.name
  branches.value = []
  activeBranchIndex.value = -1
}

const bankCategory = ref('')

const selectCategory = async (category) => {
    console.log(category.value)
  // カテゴリ確定
  selectedCategory.value = category.value

  selectedBank.value = null
  selectedBranch.value = null
  bankKeyword.value = ''
  branchKeyword.value = ''
  banks.value = []
  branches.value = []

  form.bank_name = ''
  form.bank_code = ''
  form.branch_name = ''
  form.branch_code = ''

  if (category.value === 7) {
    selectedBank.value = {
        bank_code: '9900',
        label: 'ゆうちょ銀行',
        bank_category: 7,
    }

    form.bank_code = '9900'
    form.bank_name = 'ゆうちょ銀行'

  } else {
    form.bank = null
  }
}

const selectActiveBranch = () => {
  if (activeBranchIndex.value === -1) return
  selectBranch(branches.value[activeBranchIndex.value])
}

watch(selectedBank, (bank) => {
  if (!bank) return
  form.bank_name = bank.name
  form.bank_code = bank.bank_code
})

watch(selectedBranch, (branch) => {
  if (!branch) return
  form.branch_name = branch.name
  form.branch_code = branch.branch_code
})

const onBankSelected = (bank) => {
  selectedBank.value = bank
  selectedBranch.value = null
  form.bank_name = bank.name
  form.bank_code = bank.bank_code
  form.branch_name = ''
  form.branch_code = ''
}

const onBranchSelected = (branch) => {
  selectedBranch.value = branch
  form.branch_name = branch.name
  form.branch_code = branch.branch_code
}

const fetchParams = computed(() => {
  console.log(selectedCategory.value ? { category: selectedCategory.value } : {})  
  return selectedCategory.value ? { category: selectedCategory.value } : {}
})

const extraParams = computed(() => {
    console.log(selectedBank.value);
  console.log(selectedBank.value ? { bank_code: selectedBank.value.bank_code } : {})

  return selectedBank.value ? { bank_code: selectedBank.value.bank_code } : {}
})
</script>




