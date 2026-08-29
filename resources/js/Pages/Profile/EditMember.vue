<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { User, Mail, MapPin, Building2, Lock, Check } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  member: any
  organization: { id: number; name: string; abbr: string | null } | null
  home_address: any
  shipping_address: any
  user: { id: number; email: string }
}>()

const GENDER_OPTIONS = [
  { value: 'male', label: '男性' },
  { value: 'female', label: '女性' },
  { value: 'other', label: 'その他' },
]

function normalizeDoctorNumber(value: string) {
  if (!value) return ''
  value = value.replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
  value = value.replace(/[^0-9]/g, '')
  return value.slice(0, 6)
}

const form = useForm({
  member: {
    position:        props.member.position ?? '',
    last_name:       props.member.last_name ?? '',
    first_name:      props.member.first_name ?? '',
    last_name_kana:  props.member.last_name_kana ?? '',
    first_name_kana: props.member.first_name_kana ?? '',
    gender:          props.member.gender ?? '',
    birthdate:       props.member.birthdate ?? '',
    tel:             props.member.tel ?? '',
    mobile:          props.member.mobile ?? '',
    fax:             props.member.fax ?? '',
    email:           props.member.email ?? '',
    personal_email:  props.member.personal_email ?? '',
  },
  home_address: {
    postal_code: props.home_address?.postal_code ?? '',
    address1:    props.home_address?.address1 ?? '',
    address2:    props.home_address?.address2 ?? '',
    address3:    props.home_address?.address3 ?? '',
    tel:         props.home_address?.tel ?? '',
    fax:         props.home_address?.fax ?? '',
  },
})

function submitProfile() {
  form.put(route('profile.member.update'))
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

      <!-- 所属先（読み取り専用） -->
      <div v-if="organization" class="border rounded-lg p-4 bg-muted/30 flex items-center gap-2 text-sm">
        <Building2 class="w-4 h-4 text-muted-foreground shrink-0" />
        <span class="font-medium">{{ organization.name }}</span>
        <span v-if="organization.abbr" class="text-muted-foreground text-xs">{{ organization.abbr }}</span>
      </div>

      <!-- 基本情報 -->
      <div class="border rounded-lg p-6 bg-white space-y-4">
        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
          <User class="w-4 h-4 text-blue-500" />基本情報
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">姓 <span class="text-destructive">*</span></Label>
            <Input v-model="form.member.last_name" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">名 <span class="text-destructive">*</span></Label>
            <Input v-model="form.member.first_name" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">姓（かな）</Label>
            <Input v-model="form.member.last_name_kana" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">名（かな）</Label>
            <Input v-model="form.member.first_name_kana" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">性別</Label>
            <div class="flex gap-2">
              <button
                v-for="opt in GENDER_OPTIONS"
                :key="opt.value"
                type="button"
                class="px-3 py-1.5 rounded-lg border text-sm transition-all"
                :class="form.member.gender === opt.value
                  ? 'border-primary bg-primary/5 text-primary font-medium'
                  : 'border-border bg-background text-muted-foreground hover:bg-muted'"
                @click="form.member.gender = opt.value"
              >{{ opt.label }}</button>
            </div>
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">生年月日</Label>
            <Input type="date" v-model="form.member.birthdate" />
          </div>
        </div>
      </div>

      <!-- 連絡先 -->
      <div class="border rounded-lg p-6 bg-white space-y-4">
        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
          <Mail class="w-4 h-4 text-green-500" />連絡先
        </h2>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">メールアドレス（ログインID）</Label>
          <Input v-model="form.member.email" type="email" />
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">個人メールアドレス</Label>
          <Input v-model="form.member.personal_email" type="email" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">電話番号</Label>
            <Input v-model="form.member.tel" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">携帯番号</Label>
            <Input v-model="form.member.mobile" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">FAX</Label>
            <Input v-model="form.member.fax" />
          </div>
        </div>
      </div>

      <!-- 自宅住所 -->
      <div class="border rounded-lg p-6 bg-white space-y-4">
        <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
          <MapPin class="w-4 h-4 text-orange-500" />自宅住所
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">郵便番号</Label>
            <Input v-model="form.home_address.postal_code" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">都道府県</Label>
            <Input v-model="form.home_address.address1" />
          </div>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">市区町村</Label>
          <Input v-model="form.home_address.address2" />
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">番地・建物名</Label>
          <Input v-model="form.home_address.address3" />
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
