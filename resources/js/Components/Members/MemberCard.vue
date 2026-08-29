<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { ChevronDown, Trash2, AlertTriangle, UserCheck, X } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select'
import MemberAddressForm from '@/Components/Members/MemberAddressForm.vue'
import type { Member, MemberAddressType } from '@/types'
import { MEMBER_STATUS_OPTIONS, MIN_MEMBERS } from '@/composables/useOrganizationForm'

const props = defineProps<{
  member: Member
  index: number
  memberIndex: number
  errors?: Record<string, string>
}>()

const emit = defineEmits<{
  (e: 'remove'): void
  (e: 'copy-address', from: MemberAddressType, to: MemberAddressType): void
}>()

const isOpen = ref(true)
const isRequired = computed(() => props.index < MIN_MEMBERS)

const displayName = computed(() => {
  const n = `${props.member.last_name} ${props.member.first_name}`.trim()
  return n || `先生 ${props.memberIndex}`
})

const genderOptions = [
  { value: 'male',   label: '男性' },
  { value: 'female', label: '女性' },
  { value: 'other',  label: 'その他' },
]
const normalizeDoctorNumber = (value: string) => {
  if (!value) return ''
  value = value.replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
  value = value.replace(/[^0-9]/g, '')
  return value.slice(0, 6)
}

// ──────────────────────────────────────────
// 変更点3：氏名一致のリアルタイムチェック
// 複数人（3人以上）同一人物のケースにも対応するため、
// 紐付け先は単一ではなく配列で保持する
// ──────────────────────────────────────────
const nameMatches = ref<Array<{ id: number; full_name: string; organization_name: string | null; doctor_group_id: number | null }>>([])
const checking = ref(false)
let debounceTimer: ReturnType<typeof setTimeout> | null = null

// 「同一人物として紐付ける」で選んだ相手のID一覧
const linkedMemberIds = computed<number[]>({
  get: () => props.member.same_as_member_ids ?? [],
  set: (val) => { props.member.same_as_member_ids = val },
})

const isLinked = (id: number) => linkedMemberIds.value.includes(id)

function toggleLink(id: number) {
  if (isLinked(id)) {
    linkedMemberIds.value = linkedMemberIds.value.filter(v => v !== id)
  } else {
    linkedMemberIds.value = [...linkedMemberIds.value, id]
  }
}

watch(
  () => [props.member.last_name, props.member.first_name],
  () => {
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(runCheck, 600)
  }
)

async function runCheck() {
  const lastName = props.member.last_name?.trim()
  const firstName = props.member.first_name?.trim()

  if (!lastName || !firstName) {
    nameMatches.value = []
    return
  }

  checking.value = true
  try {
    const { data } = await axios.get(route('admin.members.check-name-match'), {
      params: {
        last_name: lastName,
        first_name: firstName,
        exclude_id: props.member.id ?? null,
      },
    })
    nameMatches.value = data.matches ?? []

    // 候補から消えた相手の紐付けは自動的に解除する
    const stillPresent = nameMatches.value.map(m => m.id)
    linkedMemberIds.value = linkedMemberIds.value.filter(id => stillPresent.includes(id))
  } catch (e) {
    // 検索失敗時は静かに諦める（保存自体はブロックしない）
    nameMatches.value = []
  } finally {
    checking.value = false
  }
}
</script>

<template>
  <div class="rounded-xl border bg-card overflow-hidden">
    <!-- ヘッダー -->
    <div
      class="flex items-center justify-between px-4 py-2.5 bg-muted cursor-pointer select-none"
      @click="isOpen = !isOpen"
    >
      <div class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-full bg-[#0C447C] text-white text-xs font-medium flex items-center justify-center shrink-0">
          {{ memberIndex }}
        </div>
        <span class="text-sm font-medium">{{ displayName }}</span>
        <Badge v-if="isRequired" variant="outline" class="text-[10px] bg-amber-50 text-amber-800 border-amber-300">
          必須
        </Badge>
        <Badge v-if="linkedMemberIds.length > 0" variant="outline" class="text-[10px] bg-sky-50 text-sky-700 border-sky-300">
          <UserCheck class="w-3 h-3 mr-0.5" />紐付け済み（{{ linkedMemberIds.length }}名）
        </Badge>
      </div>
      <div class="flex items-center gap-2">
        <Button
          v-if="!isRequired"
          type="button"
          variant="ghost"
          size="sm"
          class="text-xs text-muted-foreground hover:text-destructive hover:bg-destructive/10 h-7 px-2"
          @click.stop="emit('remove')"
        >
          <Trash2 class="w-3.5 h-3.5 mr-1" />
          削除
        </Button>
        <ChevronDown
          class="w-4 h-4 text-muted-foreground transition-transform duration-200"
          :class="{ 'rotate-180': !isOpen }"
        />
      </div>
    </div>

    <!-- ボディ -->
    <div v-show="isOpen" class="p-4 space-y-3 border-t">
      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            姓 <span v-if="isRequired" class="text-destructive">*</span>
            <span class="text-[10px] text-muted-foreground/60">last_name</span>
          </Label>
          <Input v-model="member.last_name" placeholder="山田" maxlength="100" />
          <p v-if="errors?.last_name" class="text-xs text-destructive">{{ errors.last_name }}</p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            名 <span v-if="isRequired" class="text-destructive">*</span>
            <span class="text-[10px] text-muted-foreground/60">first_name</span>
          </Label>
          <Input v-model="member.first_name" placeholder="太郎" maxlength="100" />
          <p v-if="errors?.first_name" class="text-xs text-destructive">{{ errors.first_name }}</p>
        </div>
      </div>

      <!-- 変更点3：氏名一致の警告表示（複数人チェック可能） -->
      <div
        v-if="nameMatches.length > 0"
        class="rounded-lg border border-amber-300 bg-amber-50 p-3 space-y-2"
      >
        <div class="flex items-center gap-1.5 text-amber-800 text-xs font-medium">
          <AlertTriangle class="w-3.5 h-3.5" />
          同じ氏名の先生が{{ nameMatches.length }}名、既に登録されています。同一人物ならチェックしてください（複数可）
        </div>
        <label
          v-for="m in nameMatches"
          :key="m.id"
          class="flex items-center justify-between text-xs bg-white rounded-md px-2.5 py-1.5 border cursor-pointer"
        >
          <span class="flex items-center gap-2">
            <input
              type="checkbox"
              :checked="isLinked(m.id)"
              @change="toggleLink(m.id)"
              class="rounded"
            >
            {{ m.full_name }}
            <span class="text-muted-foreground">（{{ m.organization_name ?? '所属不明' }}）</span>
          </span>
          <Badge v-if="isLinked(m.id)" class="bg-sky-100 text-sky-700 text-[11px]">
            <UserCheck class="w-3 h-3 mr-0.5" />同一人物
          </Badge>
        </label>
      </div>

      <!-- 紐付け確定表示（候補一覧が消えた後も、選択済みなら分かるように） -->
      <div
        v-if="linkedMemberIds.length > 0 && nameMatches.length === 0"
        class="flex items-center justify-between rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-800"
      >
        <span class="flex items-center gap-1.5">
          <UserCheck class="w-3.5 h-3.5" />
          {{ linkedMemberIds.length }}名と同一人物として紐付け済み
        </span>
        <button type="button" class="text-sky-600 hover:text-sky-800" @click="linkedMemberIds = []">
          <X class="w-3.5 h-3.5" />
        </button>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            姓（かな） <span class="text-[10px] text-muted-foreground/60">last_name_kana</span>
          </Label>
          <Input v-model="member.last_name_kana" placeholder="やまだ" maxlength="100" />
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            名（かな） <span class="text-[10px] text-muted-foreground/60">first_name_kana</span>
          </Label>
          <Input v-model="member.first_name_kana" placeholder="たろう" maxlength="100" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            役職 <span class="text-[10px] text-muted-foreground/60">position</span>
          </Label>
          <Input v-model="member.position" placeholder="例：院長、副院長" maxlength="20" />
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            会員番号 <span class="text-[10px] text-muted-foreground/60">member_number</span>
          </Label>
          <Input v-model="member.member_number" placeholder="自動採番または手動入力" maxlength="20" />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            性別 <span class="text-[10px] text-muted-foreground/60">gender</span>
          </Label>
          <Select v-model="member.gender">
            <SelectTrigger><SelectValue placeholder="選択" /></SelectTrigger>
            <SelectContent>
              <SelectItem v-for="o in genderOptions" :key="o.value" :value="o.value">
                {{ o.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            生年月日 <span class="text-[10px] text-muted-foreground/60">birthdate</span>
          </Label>
          <Input v-model="member.birthdate" type="date" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            電話番号 <span class="text-[10px] text-muted-foreground/60">tel</span>
          </Label>
          <Input v-model="member.tel" type="tel" placeholder="03-0000-0000" maxlength="30" />
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            携帯番号 <span class="text-[10px] text-muted-foreground/60">mobile</span>
          </Label>
          <Input v-model="member.mobile" type="tel" placeholder="090-0000-0000" maxlength="30" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            メールアドレス <span class="text-[10px] text-muted-foreground/60">email</span> <span class="text-destructive">*</span>
          </Label>
          <Input v-model="member.email" type="email" placeholder="yamada@hospital.jp" maxlength="255" />
          <p v-if="errors?.email" class="text-xs text-destructive">{{ errors.email }}</p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            個人メール <span class="text-[10px] text-muted-foreground/60">personal_email</span>
          </Label>
          <Input v-model="member.personal_email" type="email" placeholder="yamada@gmail.com" maxlength="255" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            会員状況 <span class="text-[10px] text-muted-foreground/60">status_id</span>
          </Label>
          <Select v-model="member.status_id">
            <SelectTrigger><SelectValue /></SelectTrigger>
            <SelectContent>
              <SelectItem v-for="o in MEMBER_STATUS_OPTIONS" :key="o.value" :value="o.value">
                {{ o.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            会員種別 <span class="text-[10px] text-muted-foreground/60">member_type</span>
          </Label>
          <Input v-model="member.member_type" placeholder="例：正会員、準会員" maxlength="50" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            入会日 <span class="text-[10px] text-muted-foreground/60">joined_at</span>
          </Label>
          <Input v-model="member.joined_at" type="date" />
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            退会日 <span class="text-[10px] text-muted-foreground/60">withdrawn_at</span>
          </Label>
          <Input v-model="member.withdrawn_at" type="date" />
        </div>
      </div>

      <Separator />

      <MemberAddressForm
        :addresses="member.addresses"
        @copy="(from, to) => emit('copy-address', from, to)"
      />
    </div>
  </div>
</template>