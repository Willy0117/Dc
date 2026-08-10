<script setup lang="ts">
import { reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Building2, MapPin, Lock, Check, Mail } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  organization: any
  location_address: any
  shipping_address: any
  billing_address: any
  user: { id: number; email: string }
}>()

const PAYMENT_METHOD_OPTIONS = [
  { value: 1, label: '銀行振込' },
  { value: 2, label: 'クレジットカード' },
] as const

const form = useForm({
  organization: {
    url:             props.organization.url ?? '',
    rep_position:    props.organization.rep_position ?? '',
    rep_last_name:   props.organization.rep_last_name ?? '',
    rep_first_name:  props.organization.rep_first_name ?? '',
    payment_method:  props.organization.payment_method ?? 2,
  },
  location_address: {
    postal_code: props.location_address?.postal_code ?? '',
    address1:    props.location_address?.address1 ?? '',
    address2:    props.location_address?.address2 ?? '',
    address3:    props.location_address?.address3 ?? '',
    tel:         props.location_address?.tel ?? '',
    fax:         props.location_address?.fax ?? '',
    email:       props.location_address?.email ?? '',
  },
})

function submitProfile() {
  form.put(route('profile.organization.update'))
}

// ログインメールアドレス変更
const emailForm = useForm({
  email: props.user.email,
})

function submitEmail() {
  emailForm.put(route('profile.email.update'))
}

// パスワード変更
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

function submitPassword() {
  passwordForm.put(route('profile.password.update'), {
    onSuccess: () => passwordForm.reset(),
  })
}
</script>

<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">アカウント設定</p>
      <h1 class="text-xl font-semibold">プロフィール編集</h1>
    </template>

    <div class="p-6 max-w-3xl space-y-6">

      <!-- 法人情報（name/abbrは読み取り専用） -->
      <div class="border rounded-lg p-6 bg-white space-y-4">
        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
          <Building2 class="w-4 h-4 text-blue-500" />法人情報
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">法人名（変更不可）</Label>
            <Input :model-value="organization.name" disabled class="bg-muted/50" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">病院名（変更不可）</Label>
            <Input :model-value="organization.abbr" disabled class="bg-muted/50" />
          </div>
        </div>

        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">WebサイトURL</Label>
          <Input v-model="form.organization.url" type="url" placeholder="https://example-clinic.jp" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">役職</Label>
            <Input v-model="form.organization.rep_position" placeholder="院長・理事長 等" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">代表者 姓 <span class="text-destructive">*</span></Label>
            <Input v-model="form.organization.rep_last_name" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">代表者 名 <span class="text-destructive">*</span></Label>
            <Input v-model="form.organization.rep_first_name" />
          </div>
        </div>

        <div class="space-y-1.5">
          <Label class="text-xs text-muted-foreground">支払い方法</Label>
          <div class="flex gap-2">
            <button
              v-for="opt in PAYMENT_METHOD_OPTIONS"
              :key="opt.value"
              type="button"
              class="px-3 py-1.5 rounded-lg border text-sm transition-all"
              :class="form.organization.payment_method === opt.value
                ? 'border-blue-500 bg-blue-50 text-blue-800'
                : 'border-border bg-background text-muted-foreground hover:bg-muted'"
              @click="form.organization.payment_method = opt.value"
            >{{ opt.label }}</button>
          </div>
        </div>
      </div>

      <!-- 所在地情報 -->
      <div class="border rounded-lg p-6 bg-white space-y-4">
        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
          <MapPin class="w-4 h-4 text-green-500" />所在地
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">郵便番号</Label>
            <Input v-model="form.location_address.postal_code" placeholder="000-0000" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">都道府県</Label>
            <Input v-model="form.location_address.address1" />
          </div>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">市区町村</Label>
          <Input v-model="form.location_address.address2" />
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">番地・建物名</Label>
          <Input v-model="form.location_address.address3" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">電話番号</Label>
            <Input v-model="form.location_address.tel" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">メールアドレス <span class="text-destructive">*</span></Label>
            <Input v-model="form.location_address.email" type="email" />
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <Button type="button" :disabled="form.processing" @click="submitProfile">
          <Check class="w-4 h-4 mr-1.5" />プロフィールを保存
        </Button>
      </div>

      <!-- ログインメールアドレス変更 -->
      <div class="border rounded-lg p-6 bg-white space-y-4">
        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
          <Mail class="w-4 h-4 text-blue-500" />ログインメールアドレス
        </h2>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">メールアドレス（ID または メールアドレスでログイン）</Label>
          <Input v-model="emailForm.email" type="email" />
          <p v-if="emailForm.errors.email" class="text-xs text-destructive">{{ emailForm.errors.email }}</p>
        </div>
        <div class="flex justify-end">
          <Button type="button" variant="outline" :disabled="emailForm.processing" @click="submitEmail">
            メールアドレスを変更
          </Button>
        </div>
      </div>

      <!-- パスワード変更 -->
      <div class="border rounded-lg p-6 bg-white space-y-4">
        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
          <Lock class="w-4 h-4 text-amber-500" />パスワード変更
        </h2>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">現在のパスワード</Label>
          <Input v-model="passwordForm.current_password" type="password" />
          <p v-if="passwordForm.errors.current_password" class="text-xs text-destructive">{{ passwordForm.errors.current_password }}</p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">新しいパスワード</Label>
          <Input v-model="passwordForm.password" type="password" />
          <p v-if="passwordForm.errors.password" class="text-xs text-destructive">{{ passwordForm.errors.password }}</p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">新しいパスワード（確認）</Label>
          <Input v-model="passwordForm.password_confirmation" type="password" />
        </div>
        <div class="flex justify-end">
          <Button type="button" variant="outline" :disabled="passwordForm.processing" @click="submitPassword">
            パスワードを変更
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
