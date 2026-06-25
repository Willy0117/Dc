<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';

import { ref } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Checkbox } from '@/components/ui/checkbox'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const facilityName = ref('')
const gender = ref('')
const ageGroup = ref('')
const ageGroupOther = ref('')
const treatmentArea = ref('')

const punctureVessels = ref<string[]>([])
const punctureVesselsOther = ref('')
const tourniquetSites = ref<string[]>([])
const diagnoses = ref<string[]>([])
const diagnosesOther = ref('')
const complications = ref<string[]>([])
const complicationsOther = ref('')
const notes = ref('')

const genderOptions = ['男性', '女性', '不明']
const ageOptions = ['10代', '20代', '30代', '40代', '50代', '60代', '70代', '80歳以上']
const treatmentAreaOptions = ['手', '足', '肘', '肩', '膝']

const punctureVesselOptions = [
  '右鼠径動脈', '右膝窩動脈', '右足背動脈',
  '左鼠径動脈', '左膝窩動脈', '左足背動脈',
]
const tourniquetOptions = ['中枢側', '末梢側', '駆血なし']
const diagnosisOptions = [
  '変形性膝関節症', '膝蓋腱炎', '鵞足炎', '膝蓋下脂肪体炎',
  '腸脛骨靭帯炎', '膝蓋大腿靭帯炎', 'オスグッド病', 'シンスプリント',
  '有痛性外径骨', '足関節滑膜炎', '関節リウマチ', '脛骨疲労骨折',
  '前回と同じ',
]
const complicationOptions = [
  '疼痛部に明らかにチエナムが分布した',
  '疼痛部にチエナムが明らかに到達しなかった',
  '一部には到達して、一部には到達しなかった',
  '疼痛部にチエナムが到達したかが不明',
  '治療中の痛み強かった',
  '治療に時間がかかった',
  'アレルギー反応あり',
  '神経損傷',
]

function toggleCheckbox(list: string[], value: string) {
  const idx = list.indexOf(value)
  if (idx === -1) list.push(value)
  else list.splice(idx, 1)
}
</script>

<template>
  <AppLayout>
  <div class="bg-[#f5f0e8] min-h-screen p-6">
    <div class="max-w-5xl mx-auto space-y-4">

      <!-- 契約医療機関名 -->
      <Card>
        <CardContent class="pt-5 space-y-2">
          <Label class="text-sm font-medium">
            契約医療機関名・契約者名
            <span class="text-red-600 ml-1">*</span>
          </Label>
          <p class="text-xs text-muted-foreground">
            祐優会(またはAlivio)の側で入力しますので、変更しないようお願いいたします。
          </p>
          <Input v-model="facilityName" placeholder="回答を入力" />
        </CardContent>
      </Card>

      <!-- 患者性別 -->
      <Card>
        <CardContent class="pt-5 space-y-3">
          <Label class="text-sm font-medium">
            患者性別
            <span class="text-red-600 ml-1">*</span>
          </Label>
          <RadioGroup v-model="gender" class="space-y-2">
            <div
              v-for="option in genderOptions"
              :key="option"
              class="flex items-center gap-2"
            >
              <RadioGroupItem :value="option" :id="`gender-${option}`" />
              <Label :for="`gender-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
            </div>
          </RadioGroup>
        </CardContent>
      </Card>

      <!-- 年代 -->
      <Card>
        <CardContent class="pt-5 space-y-3">
          <Label class="text-sm font-medium">
            年代
            <span class="text-red-600 ml-1">*</span>
          </Label>
          <RadioGroup v-model="ageGroup" class="space-y-2">
            <div
              v-for="option in ageOptions"
              :key="option"
              class="flex items-center gap-2"
            >
              <RadioGroupItem :value="option" :id="`age-${option}`" />
              <Label :for="`age-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
            </div>
            <div class="flex items-center gap-2">
              <RadioGroupItem value="その他" id="age-other" />
              <Label for="age-other" class="font-normal cursor-pointer">その他:</Label>
              <Input
                v-model="ageGroupOther"
                class="h-7 w-40 text-sm"
                @focus="ageGroup = 'その他'"
              />
            </div>
          </RadioGroup>
        </CardContent>
      </Card>

      <!-- 治療部位 -->
      <Card>
        <CardContent class="pt-5 space-y-3">
          <Label class="text-sm font-medium">
            治療部位
            <span class="text-red-600 ml-1">*</span>
          </Label>
          <p class="text-xs text-muted-foreground">後ほど別部位の入力が可能です。</p>
          <RadioGroup v-model="treatmentArea" class="space-y-2">
            <div
              v-for="option in treatmentAreaOptions"
              :key="option"
              class="flex items-center gap-2"
            >
              <RadioGroupItem :value="option" :id="`area-${option}`" />
              <Label :for="`area-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
            </div>
          </RadioGroup>
        </CardContent>
      </Card>

      <!-- 膝の情報入力セクション -->
      <div>
        <div class="bg-[#c9a84c] rounded-t-xl px-4 py-3 text-sm font-medium text-[#412402]">
          膝の情報入力
        </div>

        <!-- 穿刺血管 -->
        <Card class="rounded-t-none border-t-0">
          <CardContent class="pt-5 space-y-3">
            <Label class="text-sm font-medium">
              【膝】穿刺血管（複数選択可）
              <span class="text-red-600 ml-1">*</span>
            </Label>
            <div
              v-for="option in punctureVesselOptions"
              :key="option"
              class="flex items-center gap-2"
            >
              <Checkbox
                :id="`pv-${option}`"
                :checked="punctureVessels.includes(option)"
                @update:checked="toggleCheckbox(punctureVessels, option)"
              />
              <Label :for="`pv-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
            </div>
            <div class="flex items-center gap-2">
              <Checkbox
                id="pv-other"
                :checked="punctureVessels.includes('その他')"
                @update:checked="toggleCheckbox(punctureVessels, 'その他')"
              />
              <Label for="pv-other" class="font-normal cursor-pointer">その他:</Label>
              <Input v-model="punctureVesselsOther" class="h-7 w-40 text-sm" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- 駆血部位 -->
      <Card>
        <CardContent class="pt-5 space-y-3">
          <Label class="text-sm font-medium">
            【膝】駆血部位
            <span class="text-red-600 ml-1">*</span>
          </Label>
          <div
            v-for="option in tourniquetOptions"
            :key="option"
            class="flex items-center gap-2"
          >
            <Checkbox
              :id="`tq-${option}`"
              :checked="tourniquetSites.includes(option)"
              @update:checked="toggleCheckbox(tourniquetSites, option)"
            />
            <Label :for="`tq-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
          </div>
        </CardContent>
      </Card>

      <!-- 病名 -->
      <Card>
        <CardContent class="pt-5 space-y-3">
          <Label class="text-sm font-medium">
            【膝】病名（複数選択可）
            <span class="text-red-600 ml-1">*</span>
          </Label>
          <div
            v-for="option in diagnosisOptions"
            :key="option"
            class="flex items-center gap-2"
          >
            <Checkbox
              :id="`dx-${option}`"
              :checked="diagnoses.includes(option)"
              @update:checked="toggleCheckbox(diagnoses, option)"
            />
            <Label :for="`dx-${option}`" class="font-normal cursor-pointer">{{ option }}</Label>
          </div>
          <div class="flex items-center gap-2">
            <Checkbox
              id="dx-other"
              :checked="diagnoses.includes('その他')"
              @update:checked="toggleCheckbox(diagnoses, 'その他')"
            />
            <Label for="dx-other" class="font-normal cursor-pointer">その他:</Label>
            <Input v-model="diagnosesOther" class="h-7 w-40 text-sm" />
          </div>
        </CardContent>
      </Card>

      <!-- 備考・コメントセクション -->
      <div>
        <div class="bg-[#c9a84c] rounded-t-xl px-4 py-3 text-sm font-medium text-[#412402]">
          備考・コメント
        </div>

        <!-- トラブル・合併症 -->
        <Card class="rounded-t-none border-t-0">
          <CardContent class="pt-5 space-y-3">
            <Label class="text-sm font-medium">
              トラブル・合併症（複数選択可）
              <span class="text-red-600 ml-1">*</span>
            </Label>
            <div
              v-for="option in complicationOptions"
              :key="option"
              class="flex items-center gap-2"
            >
              <Checkbox
                :id="`cp-${option}`"
                :checked="complications.includes(option)"
                @update:checked="toggleCheckbox(complications, option)"
              />
              <Label :for="`cp-${option}`" class="font-normal cursor-pointer text-sm leading-snug">{{ option }}</Label>
            </div>
            <div class="flex items-center gap-2">
              <Checkbox
                id="cp-other"
                :checked="complications.includes('その他')"
                @update:checked="toggleCheckbox(complications, 'その他')"
              />
              <Label for="cp-other" class="font-normal cursor-pointer">その他:</Label>
              <Input v-model="complicationsOther" class="h-7 w-40 text-sm" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- 詳細特記事項 -->
      <Card>
        <CardContent class="pt-5 space-y-2">
          <Label class="text-sm font-medium">上記の詳細特記事項</Label>
          <Textarea v-model="notes" placeholder="回答を入力" class="resize-none" rows="4" />
        </CardContent>
      </Card>

    </div>
  </div>
  </AppLayout>
</template>