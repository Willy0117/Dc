<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  User, Mail, Calendar, Building2, MapPin, GraduationCap,
  Award, Briefcase, Users, Plus, Trash2, ArrowLeft, Check,
} from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import MemberAddressFields from '@/Components/MemberAddressFields.vue'
import {
  useMemberForm,
  MEMBER_STATUS_OPTIONS,
  GENDER_OPTIONS,
} from '@/composables/useMemberForm'
import type { MemberEditProps, MemberFormData } from '@/types'

const MEMBER_TYPE_OPTIONS = [
  { value: 'regular',    label: '正会員' },
  { value: 'student',    label: '学生会員' },
  { value: 'honorary',   label: '名誉会員' },
  { value: 'supporting', label: '賛助会員' },
] as const

const props = defineProps<MemberEditProps & { errors?: Record<string, string> }>()

const emit = defineEmits<{
  (e: 'submit', data: MemberFormData): void
  (e: 'cancel'): void
}>()

const {
  form,
  isValid,
  copyHomeToShipping,
  addDegree, removeDegree,
  addRole, removeRole,
  addCommittee, removeCommittee,
} = useMemberForm(props)

type TabKey = 'personal' | 'affiliation' | 'academic'
const activeTab = ref<TabKey>('personal')

const tabs: { key: TabKey; label: string; icon: any }[] = [
  { key: 'personal',    label: '個人情報',         icon: User           },
  { key: 'affiliation', label: '所属先・住所',      icon: Building2      },
  { key: 'academic',    label: '学歴・役職',        icon: GraduationCap  },
]

// 住所コピー状態
const sameAsHome = ref(false)
function handleSameAsHome() {
  if (sameAsHome.value) copyHomeToShipping()
}

// ステータスカラー
const statusColorMap: Record<string, string> = {
  teal:  'border-emerald-500 bg-emerald-50 text-emerald-800',
  amber: 'border-amber-500  bg-amber-50   text-amber-800',
  gray:  'border-stone-400  bg-stone-50   text-stone-700',
}
function statusColor(color: string) {
  return statusColorMap[color] ?? 'border-border bg-background text-muted-foreground'
}

function normalizeDoctorNumberForSubmit(value: string | null): string | null {
  if (!value) return value
  return value.length >= 4 ? value.padStart(6, '0') : value
}

function handleSubmit() {
   if (!isValid.value) return

  if (isValid.value) emit('submit', form)
}

const normalizeDoctorNumber = (value: string) => {
  if (!value) return ''
  value = value.replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
  value = value.replace(/[^0-9]/g, '')
  return value.slice(0, 6)
}
</script>

<template>
  <div class="flex flex-col h-full">

    <!-- ─── ヘッダー ──────────────────────────── -->
    <div class="flex items-center justify-between px-6 py-3 border-b bg-background sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <Button type="button" variant="ghost" size="sm" class="gap-1.5 text-muted-foreground" @click="emit('cancel')">
          <ArrowLeft class="w-4 h-4" />
          戻る
        </Button>
        <Separator orientation="vertical" class="h-5" />
        <span class="text-sm text-muted-foreground">会員管理</span>
      </div>
      <div class="flex items-center gap-2">
        <Button type="button" variant="outline" @click="emit('cancel')">
          キャンセル
        </Button>
        <Button
          type="button"
          :disabled="!isValid"
          class="bg-[#0C447C] hover:bg-[#185FA5] text-white gap-1.5"
          @click="handleSubmit"
        >
          <Check class="w-4 h-4" />
          保存する
        </Button>
      </div>
    </div>

    <!-- ─── メインエリア ──────────────────────── -->
    <div class="flex flex-1 min-h-0">

      <!-- 縦タブ -->
      <nav class="w-44 shrink-0 border-r bg-muted/30 py-4 flex flex-col gap-1 px-2">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-left w-full group"
          :class="activeTab === tab.key
            ? 'bg-white border border-border text-foreground shadow-sm border-l-[3px] border-l-[#0C447C]'
            : 'text-muted-foreground hover:bg-white/60 hover:text-foreground'"
          @click="activeTab = tab.key"
        >
          <component
            :is="tab.icon"
            class="w-4 h-4 shrink-0 transition-colors"
            :class="activeTab === tab.key ? 'text-[#0C447C]' : 'text-muted-foreground group-hover:text-foreground'"
          />
          {{ tab.label }}
        </button>
      </nav>

      <!-- コンテンツ -->
      <div class="flex-1 overflow-y-auto px-8 py-6">

        <!-- ========== 個人情報 ========== -->
        <div v-show="activeTab === 'personal'" class="max-w-4xl space-y-6">

          <!-- 基本情報 -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
              <User class="w-3.5 h-3.5" /> 基本情報
            </p>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">会員番号 <span class="text-[10px] text-muted-foreground/60 ml-0.5">member_number</span></Label>
                <Input v-model="form.member.member_number" placeholder="例: M-00001" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">役職 <span class="text-[10px] text-muted-foreground/60 ml-0.5">position</span></Label>
                <Input v-model="form.member.position" placeholder="例: 理事長" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">入会日 <span class="text-[10px] text-muted-foreground/60 ml-0.5">joined_at</span></Label>
                <Input type="date" v-model="form.member.joined_at" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">退会日 <span class="text-[10px] text-muted-foreground/60 ml-0.5">withdrawn_at</span></Label>
                <Input type="date" v-model="form.member.withdrawn_at" />
              </div>
            </div>
          </section>

          <!-- 会員種別 -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">会員種別</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="opt in MEMBER_TYPE_OPTIONS"
                :key="opt.value"
                type="button"
                class="px-3 py-1.5 rounded-lg border text-sm transition-all"
                :class="form.member.member_type === opt.value
                  ? 'border-[#0C447C] bg-blue-50 text-[#0C447C] font-medium'
                  : 'border-border bg-background text-muted-foreground hover:bg-muted'"
                @click="form.member.member_type = opt.value"
              >
                {{ opt.label }}
              </button>
            </div>
          </section>

          <!-- 会員状況 -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">会員状況</p>
            <div class="flex gap-2">
              <button
                v-for="opt in MEMBER_STATUS_OPTIONS"
                :key="opt.value"
                type="button"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm transition-all"
                :class="form.member.status_id === opt.value
                  ? statusColor(opt.color)
                  : 'border-border bg-background text-muted-foreground hover:bg-muted'"
                @click="form.member.status_id = opt.value"
              >
                {{ opt.label }}
              </button>
            </div>
          </section>

          <!-- 氏名 -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
              <User class="w-3.5 h-3.5" /> 氏名
            </p>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">
                  姓 <span class="text-destructive">*</span>
                </Label>
                <Input v-model="form.member.last_name" placeholder="山田" />
                <p v-if="props.errors?.['member.last_name']" class="text-xs text-destructive">{{ props.errors['member.last_name'] }}</p>
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">
                  名 <span class="text-destructive">*</span>
                </Label>
                <Input v-model="form.member.first_name" placeholder="太郎" />
                <p v-if="props.errors?.['member.first_name']" class="text-xs text-destructive">{{ props.errors['member.first_name'] }}</p>
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">姓（かな）</Label>
                <Input v-model="form.member.last_name_kana" placeholder="やまだ" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">名（かな）</Label>
                <Input v-model="form.member.first_name_kana" placeholder="たろう" />
              </div>
            </div>
          </section>

          <!-- 基本属性 -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
              <Calendar class="w-3.5 h-3.5" /> 基本属性
            </p>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <Label class="text-xs text-muted-foreground">性別</Label>
                <div class="flex gap-2">
                  <button
                    v-for="opt in GENDER_OPTIONS"
                    :key="opt.value"
                    type="button"
                    class="px-3 py-1.5 rounded-lg border text-sm transition-all"
                    :class="form.member.gender === opt.value
                      ? 'border-[#0C447C] bg-blue-50 text-[#0C447C] font-medium'
                      : 'border-border bg-background text-muted-foreground hover:bg-muted'"
                    @click="form.member.gender = opt.value"
                  >
                    {{ opt.label }}
                  </button>
                </div>
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">生年月日</Label>
                <Input type="date" v-model="form.member.birthdate" />
              </div>
            </div>
          </section>

          <!-- 連絡先 -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
              <Mail class="w-3.5 h-3.5" /> 連絡先
            </p>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1 col-span-2">
                <Label class="text-xs text-muted-foreground">
                  メールアドレス（ログインID）<span class="text-destructive">*</span>
                </Label>
                <Input type="email" v-model="form.member.email" placeholder="example@example.com" />
                <p v-if="props.errors?.['member.email']" class="text-xs text-destructive">{{ props.errors['member.email'] }}</p>
              </div>
              <div class="space-y-1 col-span-2">
                <Label class="text-xs text-muted-foreground">個人メールアドレス</Label>
                <Input type="email" v-model="form.member.personal_email" placeholder="personal@example.com" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">電話番号</Label>
                <Input v-model="form.member.tel" placeholder="03-0000-0000" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">携帯番号</Label>
                <Input v-model="form.member.mobile" placeholder="090-0000-0000" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">FAX</Label>
                <Input v-model="form.member.fax" placeholder="03-0000-0000" />
              </div>
            </div>
          </section>
        </div>

        <!-- ========== 所属先・住所 ========== -->
        <div v-show="activeTab === 'affiliation'" class="max-w-4xl space-y-6">

          <!-- 所属組織（表示のみ・Inertia propsから） -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
              <Building2 class="w-3.5 h-3.5" /> 所属先
            </p>
            <div
              v-if="props.organization"
              class="flex items-center gap-2 px-3 py-2.5 rounded-lg border bg-muted/40 text-sm text-foreground"
            >
              <Building2 class="w-4 h-4 text-muted-foreground shrink-0" />
              <span class="font-medium">{{ props.organization.name }}</span>
              <span v-if="props.organization.abbr" class="text-muted-foreground text-xs">{{ props.organization.abbr }}</span>
            </div>
            <p v-else class="text-sm text-muted-foreground">所属組織なし</p>
          </section>

          <!-- 自宅住所 -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5" /> 自宅住所
            </p>
            <MemberAddressFields :address="form.home_address" />
          </section>

          <!-- 送付先住所 -->
          <section class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5" /> 送付先住所
              </p>
              <label class="flex items-center gap-2 text-xs text-muted-foreground cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="sameAsHome"
                  class="rounded border-border"
                  @change="handleSameAsHome"
                />
                自宅住所と同じ
              </label>
            </div>
            <div v-if="sameAsHome" class="flex items-center gap-2 px-3 py-2.5 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-800">
              <Check class="w-4 h-4 shrink-0" />
              自宅住所を送付先として使用します
            </div>
            <div v-else>
              <MemberAddressFields :address="form.shipping_address" />
            </div>
          </section>
        </div>

        <!-- ========== 学歴・役職 ========== -->
        <div v-show="activeTab === 'academic'" class="max-w-4xl space-y-6">

          <!-- 最終学歴 -->
          <section class="space-y-3">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
              <GraduationCap class="w-3.5 h-3.5" /> 最終学歴
            </p>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1 col-span-2">
                <Label class="text-xs text-muted-foreground">学校名</Label>
                <Input v-model="form.education.school_name" placeholder="○○大学" />
              </div>
              <div class="space-y-1 col-span-2">
                <Label class="text-xs text-muted-foreground">学部・学科名</Label>
                <Input v-model="form.education.faculty" placeholder="医学部 医学科" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">卒業（予定）年月</Label>
                <Input v-model="form.education.graduated_at" placeholder="2000-03" />
              </div>
            </div>
          </section>

          <!-- 取得学位 -->
          <section class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                <Award class="w-3.5 h-3.5" /> 取得学位
              </p>
              <Button type="button" variant="outline" size="sm" class="gap-1 text-xs" :disabled="form.degrees.length >= 5" @click="addDegree">
                <Plus class="w-3.5 h-3.5" /> 追加
              </Button>
            </div>
            <div v-if="form.degrees.length === 0" class="flex flex-col items-center gap-2 py-6 text-muted-foreground/50">
              <Award class="w-8 h-8" />
              <p class="text-sm">学位を追加してください</p>
            </div>
            <div v-for="(degree, i) in form.degrees" :key="i" class="rounded-lg border bg-muted/20 p-4 space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">学位 {{ i + 1 }}</span>
                <Button type="button" variant="ghost" size="sm" class="text-destructive hover:text-destructive hover:bg-destructive/10 h-7 px-2" @click="removeDegree(i)">
                  <Trash2 class="w-3.5 h-3.5" />
                </Button>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1 col-span-2">
                  <Label class="text-xs text-muted-foreground">学位名</Label>
                  <Input v-model="degree.degree" placeholder="医学博士" />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs text-muted-foreground">取得年月</Label>
                  <Input v-model="degree.obtained_at" placeholder="2005-03" />
                </div>
              </div>
            </div>
          </section>

          <!-- 学会役職歴 -->
          <section class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                <Briefcase class="w-3.5 h-3.5" /> 学会役職歴
              </p>
              <Button type="button" variant="outline" size="sm" class="gap-1 text-xs" @click="addRole">
                <Plus class="w-3.5 h-3.5" /> 追加
              </Button>
            </div>
            <div v-if="form.roles.length === 0" class="flex flex-col items-center gap-2 py-6 text-muted-foreground/50">
              <Briefcase class="w-8 h-8" />
              <p class="text-sm">役職歴を追加してください</p>
            </div>
            <div v-for="(role, i) in form.roles" :key="i" class="rounded-lg border bg-muted/20 p-4 space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">役職 {{ i + 1 }}</span>
                <Button type="button" variant="ghost" size="sm" class="text-destructive hover:text-destructive hover:bg-destructive/10 h-7 px-2" @click="removeRole(i)">
                  <Trash2 class="w-3.5 h-3.5" />
                </Button>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1 col-span-2">
                  <Label class="text-xs text-muted-foreground">担当役職</Label>
                  <Input v-model="role.role" placeholder="理事" />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs text-muted-foreground">開始年月</Label>
                  <Input v-model="role.started_at" placeholder="2010-04" />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs text-muted-foreground">終了年月</Label>
                  <Input v-model="role.ended_at" placeholder="2014-03" />
                </div>
              </div>
            </div>
          </section>

          <!-- 学会委員歴 -->
          <section class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                <Users class="w-3.5 h-3.5" /> 学会委員歴
              </p>
              <Button type="button" variant="outline" size="sm" class="gap-1 text-xs" @click="addCommittee">
                <Plus class="w-3.5 h-3.5" /> 追加
              </Button>
            </div>
            <div v-if="form.committees.length === 0" class="flex flex-col items-center gap-2 py-6 text-muted-foreground/50">
              <Users class="w-8 h-8" />
              <p class="text-sm">委員歴を追加してください</p>
            </div>
            <div v-for="(committee, i) in form.committees" :key="i" class="rounded-lg border bg-muted/20 p-4 space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">委員 {{ i + 1 }}</span>
                <Button type="button" variant="ghost" size="sm" class="text-destructive hover:text-destructive hover:bg-destructive/10 h-7 px-2" @click="removeCommittee(i)">
                  <Trash2 class="w-3.5 h-3.5" />
                </Button>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1 col-span-2">
                  <Label class="text-xs text-muted-foreground">担当委員</Label>
                  <Input v-model="committee.committee" placeholder="編集委員会" />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs text-muted-foreground">開始年月</Label>
                  <Input v-model="committee.started_at" placeholder="2010-04" />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs text-muted-foreground">終了年月</Label>
                  <Input v-model="committee.ended_at" placeholder="2014-03" />
                </div>
              </div>
            </div>
          </section>

        </div>
      </div>
    </div>
  </div>
</template>