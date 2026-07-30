<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import {
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps({
  open: { type: Boolean, default: false },
  selectedSet: { type: Object, default: null },
})

const emit = defineEmits(['update:open'])

const form = useForm({
  membership_status: '',
  name: '',
  affiliation: '',
  phone: '',
  email: '',
  occupation: '',
  occupation_other: '',
  bed_count: '',
})

const occupationOptions = [
  { value: '1', label: '看護師' },
  { value: '2', label: '薬剤師' },
  { value: '3', label: '医師' },
  { value: '4', label: '診療放射線技師' },
  { value: '5', label: '歯科医師' },
  { value: '6', label: '助産師' },
  { value: '7', label: '保健師' },
  { value: '8', label: '臨床検査技師' },
  { value: '9', label: '臨床工学技士' },
  { value: '10', label: '理学療法士' },
  { value: '11', label: '作業療法士' },
  { value: '12', label: '臨床心理士' },
  { value: '13', label: '歯科衛生士' },
  { value: '14', label: '栄養士・管理栄養士' },
  { value: '15', label: '救急救命士' },
  { value: '16', label: '診療情報管理士' },
  { value: '17', label: '医療ソーシャルワーカー' },
  { value: '18', label: '介護士・介護福祉士' },
  { value: '19', label: 'その他事務' },
  { value: '20', label: '上記以外' },
]

const bedCountOptions = [
  { value: '1', label: '無床' },
  { value: '2', label: '1床～49床' },
  { value: '3', label: '50床～99床' },
  { value: '4', label: '100床～199床' },
  { value: '5', label: '200床～299床' },
  { value: '6', label: '300床～399床' },
  { value: '7', label: '400床～499床' },
  { value: '8', label: '500床以上' },
]

const agreed = ref(false)

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    form.reset()
    form.clearErrors()
    agreed.value = false
  }
})

function close(value) {
  emit('update:open', value)
}

function submit() {
  if (!props.selectedSet) return
  if (!agreed.value) return
  if (!form.membership_status || !form.occupation || !form.bed_count) return
  if (form.occupation === '20' && !form.occupation_other) return
  form.post(route('products.checkout', props.selectedSet.id))
}
</script>

<template>
  <Dialog :open="open" @update:open="close">
    <DialogContent class="max-w-md max-h-[85vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>お申込み情報の入力</DialogTitle>
      </DialogHeader>

      <div v-if="selectedSet" class="bg-sky-50 border border-sky-200 rounded-xl p-4 mb-2">
        <p class="text-xs text-sky-600 font-semibold mb-1">お申込みのセット</p>
        <p class="text-lg font-extrabold text-sky-900">
          セット{{ selectedSet.name }}：{{ selectedSet.category }}
        </p>
        <p class="text-sm text-slate-600">{{ selectedSet.theme }}</p>
        <p class="text-base font-bold text-sky-800 mt-2">
          ¥{{ selectedSet.price_jpy.toLocaleString() }}（税込）
        </p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label>会員の有無について</Label>
          <RadioGroup v-model="form.membership_status" class="flex gap-4 mt-1">
            <div class="flex items-center gap-1.5">
              <RadioGroupItem id="membership-member" value="member" />
              <Label for="membership-member" class="text-sm font-normal cursor-pointer">会員</Label>
            </div>
            <div class="flex items-center gap-1.5">
              <RadioGroupItem id="membership-non-member" value="non_member" />
              <Label for="membership-non-member" class="text-sm font-normal cursor-pointer">非会員</Label>
            </div>
          </RadioGroup>
          <p v-if="form.errors.membership_status" class="text-sm text-red-600">{{ form.errors.membership_status }}</p>
        </div>

        <div>
          <Label for="dlg-name">
            受講者氏名
            <span class="block text-xs font-normal text-gray-500 mt-0.5">
              ※受講証明書に記載されますので、お間違いのないようご入力ください
            </span>
          </Label>
          <Input id="dlg-name" v-model="form.name" placeholder="例：山田 太郎" required />
          <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <div>
          <Label for="dlg-affiliation">所属</Label>
          <Input id="dlg-affiliation" v-model="form.affiliation" placeholder="例：〇〇病院 医療安全管理室" required />
          <p v-if="form.errors.affiliation" class="text-sm text-red-600">{{ form.errors.affiliation }}</p>
        </div>

        <div>
          <Label for="dlg-phone">TEL</Label>
          <Input id="dlg-phone" v-model="form.phone" placeholder="例：03-0000-0000" required />
          <p v-if="form.errors.phone" class="text-sm text-red-600">{{ form.errors.phone }}</p>
        </div>

        <div>
          <Label for="dlg-email">E-mail（視聴用URL送付先）</Label>
          <Input id="dlg-email" type="email" v-model="form.email" placeholder="例：sample@example.com" required />
          <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
        </div>

        <div>
          <Label for="dlg-occupation">現在の職種について</Label>
          <Select v-model="form.occupation">
            <SelectTrigger id="dlg-occupation" class="w-full">
              <SelectValue placeholder="選択してください" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="opt in occupationOptions" :key="opt.value" :value="opt.value">
                {{ opt.value }}　{{ opt.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="form.errors.occupation" class="text-sm text-red-600">{{ form.errors.occupation }}</p>

          <div v-if="form.occupation === '20'" class="mt-2">
            <Input
              v-model="form.occupation_other"
              placeholder="具体的にご記入ください"
              required
            />
            <p v-if="form.errors.occupation_other" class="text-sm text-red-600">{{ form.errors.occupation_other }}</p>
          </div>
        </div>

        <div>
          <Label>病床数について</Label>
          <RadioGroup v-model="form.bed_count" class="grid grid-cols-2 gap-x-4 gap-y-1.5 mt-1">
            <div v-for="opt in bedCountOptions" :key="opt.value" class="flex items-center gap-1.5">
              <RadioGroupItem :id="`bed-count-${opt.value}`" :value="opt.value" />
              <Label :for="`bed-count-${opt.value}`" class="text-sm font-normal cursor-pointer">{{ opt.label }}</Label>
            </div>
          </RadioGroup>
          <p v-if="form.errors.bed_count" class="text-sm text-red-600">{{ form.errors.bed_count }}</p>
        </div>

        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 text-xs">
          ご入力内容に間違いがないか確認後、決済フォームへお進みください。
          決済完了画面に表示される「視聴サイトへすすむ」ボタンから動画をご視聴いただけます。
        </div>

         <!-- ご利用にあたっての注意事項（送信ボタンの直前に配置） -->
        <div class="border border-slate-200 rounded-lg">
          <label class="block px-3 pt-3 pb-1 text-xs font-semibold text-slate-700">
            下記の行為は禁止事項といたします。
          </label>
          <div class="max-h-48 overflow-y-auto px-3 pb-3 space-y-3 text-xs text-slate-600 leading-relaxed">
            <ul class="list-disc pl-4 space-y-1">
              <li>動画データのすべてまたは一部を不正に電磁機器にデジタル情報として保存する行為</li>
              <li>動画データで用いられた動画ファイルの不正取得・不正印刷する行為</li>
              <li>ご自身の視聴URLを譲渡もしくはそれらを用いて他者へ講演内容を閲覧させる行為</li>
              <li>スクリーンショット等を用いてのデータ流用、書籍等への転用等の行為</li>
            </ul>

            <p>
              動画再生や視聴には大量のデータ（パケット）通信を行うため、携帯・通信キャリア各社にて通信料が発生します。
              データ通信量が一定の基準に達した時点で、通信会社での通信速度制限が行われることがあります。
              スマートフォンやタブレットでご視聴の場合は、Wi-fi環境でのご利用を推奨します。
              なお、発生したデータ通信費用について弊会は一切の責任を負いかねます。
            </p>

            <p>
              動画の視聴にあたり生じた、いかなる損害についても弊会は一切の責任を負いかねます。
              アクセスが集中した場合は、映像をスムーズに視聴できない場合がございます。
              時間をおいてから再度アクセスをお願いします。
            </p>
          </div>

          <div class="flex items-start gap-2 border-t border-slate-200 bg-slate-50 px-3 py-2.5 rounded-b-lg">
            <Checkbox id="agree-checkbox" v-model="agreed" class="mt-0.5" />
            <Label for="agree-checkbox" class="text-xs font-semibold text-slate-700 cursor-pointer">
              同意する
            </Label>
          </div>
        </div>

        <Button
          type="submit"
          class="w-full transition-opacity"
          :class="agreed ? '' : 'opacity-40 grayscale'"
          :disabled="form.processing || !agreed || !form.membership_status || !form.occupation || !form.bed_count || (form.occupation === '20' && !form.occupation_other)"
        >
          決済フォームへ進む
        </Button>
        <p v-if="!agreed" class="text-xs text-center text-slate-400 -mt-2">
          注意事項への同意にチェックを入れると送信できます
        </p>
      </form>
    </DialogContent>
  </Dialog>
</template>