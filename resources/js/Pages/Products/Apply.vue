<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle,
} from '@/components/ui/dialog'

// NOTE: component/ui の実際のパス・props名(Dialog、Checkbox等)はプロジェクトの実装に合わせて調整してください。
// Checkboxが未導入の場合は `npx shadcn-vue@latest add checkbox` で追加してください。

const props = defineProps({
  open: { type: Boolean, default: false },
  selectedSet: { type: Object, default: null }, // { id, name, category, theme, price_jpy }
})

const emit = defineEmits(['update:open'])

const form = useForm({
  name: '',
  affiliation: '',
  phone: '',
  email: '',
})

const agreed = ref(false)

// ダイアログが開くたびにフォームをリセット
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
  form.post(route('products.checkout', props.selectedSet.id))
}
</script>

<template>
  <Dialog :open="open" @update:open="close">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>お申込み情報の入力</DialogTitle>
      </DialogHeader>

      <!-- 選択中の商品を大きく表示（誤操作防止） -->
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

      <!-- ご利用にあたっての注意事項 -->
      <div class="border border-slate-200 rounded-lg mb-2">
        <div class="max-h-48 overflow-y-auto p-3 space-y-3 text-xs text-slate-600 leading-relaxed">
          <div>
            <p class="font-semibold text-slate-700 mb-1">下記の行為は禁止事項といたします。</p>
            <ul class="list-disc pl-4 space-y-1">
              <li>動画データのすべてまたは一部を不正に電磁機器にデジタル情報として保存する行為</li>
              <li>動画データで用いられた動画ファイルの不正取得・不正印刷する行為</li>
              <li>ご自身の視聴URLを譲渡もしくはそれらを用いて他者へ講演内容を閲覧させる行為</li>
              <li>スクリーンショット等を用いてのデータ流用、書籍等への転用等の行為</li>
            </ul>
          </div>

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

        <label class="flex items-start gap-2 border-t border-slate-200 bg-slate-50 px-3 py-2.5 rounded-b-lg cursor-pointer">
          <Checkbox v-model:checked="agreed" class="mt-0.5" />
          <span class="text-xs font-semibold text-slate-700">
            上記の注意事項・禁止事項に同意します
          </span>
        </label>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
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

        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 text-xs">
          ご入力内容に間違いがないか確認後、決済フォームへお進みください。
          決済完了画面に表示される「視聴サイトへすすむ」ボタンから動画をご視聴いただけます。
        </div>

        <Button type="submit" class="w-full" :disabled="form.processing || !agreed">
          決済フォームへ進む
        </Button>
        <p v-if="!agreed" class="text-xs text-center text-slate-400 -mt-2">
          注意事項への同意にチェックを入れると送信できます
        </p>
      </form>
    </DialogContent>
  </Dialog>
</template>