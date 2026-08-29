<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">症例報告</p>
      <h1 class="text-xl font-semibold">動注治療レポート入力</h1>
    </template>

    <div class="p-6 max-w-4xl mx-auto">

      <!-- ステップインジケーター -->
      <div class="flex items-center gap-2 mb-8">
        <template v-for="(label, i) in stepLabels" :key="i">
          <div class="flex items-center gap-2">
            <div
              class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition-colors"
              :class="i + 1 === currentStep
                ? 'bg-primary text-primary-foreground'
                : i + 1 < currentStep
                  ? 'bg-primary/20 text-primary'
                  : 'bg-muted text-muted-foreground'"
            >
              <Check v-if="i + 1 < currentStep" class="w-3.5 h-3.5" />
              <span v-else>{{ i + 1 }}</span>
            </div>
            <span class="text-xs hidden sm:block" :class="i + 1 === currentStep ? 'text-foreground font-medium' : 'text-muted-foreground'">
              {{ label }}
            </span>
          </div>
          <div v-if="i < stepLabels.length - 1" class="flex-1 h-px bg-border" />
        </template>
      </div>

      <!-- Step 1: 基本情報 -->
      <div v-if="currentStep === 1" class="space-y-6">
        <!-- 先生選択：
             ・先生（member）ログインの場合 → 自分自身が確定しているので非表示（変更点9・③）
             ・病院（organization）ログインの場合 → 必須選択（③） -->
        <div class="space-y-2" v-if="!isMemberLogin && props.members?.length">
          <Label>先生 <span class="text-destructive">*</span></Label>
          <Select v-model="form.member_id">
            <SelectTrigger :class="form.errors.member_id ? 'border-destructive' : ''">
              <SelectValue placeholder="選択してください" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="m in props.members" :key="m.id" :value="m.id">
                {{ m.last_name }} {{ m.first_name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="form.errors.member_id" class="text-xs text-destructive">
            {{ form.errors.member_id }}
          </p>
        </div>

        <div class="space-y-2">
          <Label>患者性別 <span class="text-destructive">*</span></Label>
          <div class="flex gap-4">
            <label v-for="g in ['男性', '女性', '不明']" :key="g" class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="form.patient_gender" :value="g" class="accent-primary" />
              <span class="text-sm">{{ g }}</span>
            </label>
          </div>
        </div>

        <div class="space-y-2">
          <Label>年代 <span class="text-destructive">*</span></Label>
          <Select v-model="form.patient_age_group">
            <SelectTrigger>
              <SelectValue placeholder="選択してください" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="age in ageGroups" :key="age" :value="age">{{ age }}</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label>治療部位 <span class="text-destructive">*</span></Label>
          <div class="grid grid-cols-5 gap-2">
            <button
              v-for="area in treatmentAreas"
              :key="area"
              type="button"
              @click="form.treatment_area = area"
              class="py-3 rounded-lg border text-sm font-medium transition-colors"
              :class="form.treatment_area === area
                ? 'bg-primary text-primary-foreground border-primary'
                : 'bg-background hover:bg-muted border-border'"
            >
              {{ area }}
            </button>
          </div>
        </div>
      </div>

      <!-- Step 2: 部位別フォーム（DBから動的生成） -->
      <div v-if="currentStep === 2" class="space-y-6">
        <template v-for="(fieldDef, fieldName) in areaFields" :key="fieldName">
          <CheckboxGroup
            v-if="fieldDef.type === 'checkbox'"
            :label="fieldName"
            :options="fieldDef.options"
            v-model="form.details[fieldName]"
            :required="true"
          />
          <RadioGroup
            v-else-if="fieldDef.type === 'radio'"
            :label="fieldName"
            :options="fieldDef.options"
            v-model="form.details[fieldName]"
            :required="true"
          />
          <div v-else-if="fieldDef.type === 'text'" class="space-y-2">
            <Label>{{ fieldName }}</Label>
            <Input v-model="form.details[fieldName]" />
          </div>
        </template>
      </div>

      <!-- Step 3: 備考・コメント -->
      <div v-if="currentStep === 3" class="space-y-6">
        <CheckboxGroup
          label="トラブル・合併症"
          :options="complicationOptions"
          v-model="form.complication_types"
          :required="true"
        />
        <div class="space-y-2">
          <Label>上記の詳細特記事項</Label>
          <Textarea v-model="form.notes" placeholder="回答を入力" rows="4" />
        </div>
      </div>

      <!-- ナビゲーションボタン -->
      <div class="flex justify-between mt-8">
        <Button variant="outline" @click="currentStep--" :disabled="currentStep === 1">
          <ChevronLeft class="w-4 h-4 mr-1" />戻る
        </Button>
        <Button v-if="currentStep < 3" @click="nextStep" :disabled="!canProceed">
          次へ<ChevronRight class="w-4 h-4 ml-1" />
        </Button>
        <Button v-else @click="submit" :disabled="form.processing || !canProceed">
          <Send class="w-4 h-4 mr-1" />送信
        </Button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { Check, ChevronLeft, ChevronRight, Send } from 'lucide-vue-next'
import AppLayout     from '@/Layouts/AppLayout.vue'
import CheckboxGroup from '@/Components/Form/CheckboxGroup.vue'
import RadioGroup    from '@/Components/Form/RadioGroup.vue'
import { Button }   from '@/components/ui/button'
import { Input }    from '@/components/ui/input'
import { Label }    from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  options: { type: Object, default: () => ({}) },
  members: { type: Array, default: () => [] },
})

// ログイン種別判定（変更点9・③）：
// user.type === 2 の場合は先生本人ログイン、1 の場合は病院ログイン
const { props: pageProps } = usePage()
const authUser = pageProps.auth?.user
const isMemberLogin = computed(() => authUser?.type === 2)

const currentStep = ref(1)
const stepLabels  = ['基本情報', '治療詳細', '備考・コメント']

const ageGroups      = ['10代以下', '10代', '20代', '30代', '40代', '50代', '60代', '70代', '80代', '90代以上']
const treatmentAreas = ['手', '足', '肘', '肩', '膝']

const areaFields = computed(() => props.options?.[form.treatment_area] ?? {})

const complicationOptions = computed(() => props.options?.['共通']?.['トラブル・合併症']?.options ?? [])

// useForm化：バリデーションエラーが form.errors に自動で入るようになる
const form = useForm({
  member_id:          null,
  patient_gender:     '',
  patient_age_group:  '',
  treatment_area:     '',
  details:            {},
  complication_types: [],
  notes:              '',
})

// 先生ログインの場合、自分自身のmember_idを自動セットする（選択不要）
onMounted(() => {
  if (isMemberLogin.value && authUser?.member?.id) {
    form.member_id = authUser.member.id
  }
})

const resetDetails = () => {
  const fields  = props.options?.[form.treatment_area] ?? {}
  const details = {}
  for (const [fieldName, fieldDef] of Object.entries(fields)) {
    details[fieldName] = fieldDef.type === 'checkbox' ? [] : ''
  }
  form.details = details
}

const canProceed = computed(() => {
  if (currentStep.value === 1) {
    // 病院ログインの場合のみ、先生選択を必須条件に含める（③）
    const memberOk = isMemberLogin.value || !!form.member_id
    return memberOk && form.patient_gender && form.patient_age_group && form.treatment_area
  }
  if (currentStep.value === 2) {
    for (const [fieldName, fieldDef] of Object.entries(areaFields.value)) {
      const val = form.details[fieldName]
      if (fieldDef.type === 'checkbox' && (!val || val.length === 0)) return false
      if (fieldDef.type === 'radio' && !val) return false
    }
    return true
  }
  return true
})

const nextStep = () => {
  if (!canProceed.value) return
  if (currentStep.value === 1) resetDetails()
  currentStep.value++
}

const submit = () => {
  form.post(route('reports.store'), {
    // Step1に先生未選択エラーが返ってきた場合、そこに戻して見せる
    onError: (errors) => {
      if (errors.member_id) currentStep.value = 1
    },
  })
}
</script>