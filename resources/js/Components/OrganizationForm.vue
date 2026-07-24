<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  Building2, FileText, MapPin, Stethoscope, Plus, Check, ArrowLeft,
} from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import AddressForm from '@/Components/AddressForm.vue'
import MemberCard from '@/Components/Members/MemberCard.vue'
import {
  useOrganizationForm,
  CONTRACT_STATUS_OPTIONS,
  PAYMENT_METHOD_OPTIONS,
  MIN_MEMBERS,
} from '@/composables/useOrganizationForm'
import type { OrganizationEditProps, OrganizationFormData } from '@/types'

console.log('OrganizationForm setup start')
const props = defineProps<OrganizationEditProps>()
console.log('OrganizationForm props:', props)

const emit = defineEmits<{
  (e: 'submit', data: OrganizationFormData): void
  (e: 'cancel'): void
}>()

const {
  form,
  isValid,
  addMember,
  removeMember,
  copyOrgAddress,
  copyMemberAddress,
} = useOrganizationForm(props)

type TabKey = 'basic' | 'contract' | 'address' | 'members'
const activeTab = ref<TabKey>('basic')

const tabs: { key: TabKey; label: string; icon: any }[] = [
  { key: 'basic',    label: '法人情報', icon: Building2   },
  { key: 'contract', label: '契約情報', icon: FileText    },
  { key: 'address',  label: '住所情報', icon: MapPin      },
  { key: 'members',  label: '先生登録', icon: Stethoscope },
]

const memberCount = computed(() => form.members.length)

function normalizeDoctorNumberForSubmit(value: string | null): string | null {
  if (!value) return value
  return value.length >= 4 ? value.padStart(6, '0') : value
}

function handleSubmit() {
  if (!isValid.value) return

  form.members = form.members.map(m => ({
    ...m,
    doctor_number: normalizeDoctorNumberForSubmit(m.doctor_number),
  }))

  emit('submit', form)
}

</script>

<template>
  <div class="flex flex-col h-full">

    <!-- ─── 上部ヘッダー ──────────────────────── -->
    <div class="flex items-center justify-between px-6 py-3 border-b bg-background sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <Button type="button" variant="ghost" size="sm" class="gap-1.5 text-muted-foreground" @click="emit('cancel')">
          <ArrowLeft class="w-4 h-4" />
          戻る
        </Button>
        <Separator orientation="vertical" class="h-5" />
        <span class="text-sm text-muted-foreground">契約先管理</span>
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

    <!-- ─── メインエリア（縦タブ + コンテンツ） ── -->
    <div class="flex flex-1 min-h-0">

      <!-- 縦タブ -->
      <nav class="w-44 shrink-0 border-r bg-muted/30 py-4 flex flex-col gap-1 px-2">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-left w-full group"
          :class="activeTab === tab.key
            ? 'bg-white border border-border text-foreground shadow-sm border-l-[3px] border-l-emerald-500'
            : 'text-muted-foreground hover:bg-white/60 hover:text-foreground'"
          @click="activeTab = tab.key"
        >
          <component
            :is="tab.icon"
            class="w-4 h-4 shrink-0 transition-colors"
            :class="activeTab === tab.key ? 'text-emerald-600' : 'text-muted-foreground group-hover:text-foreground'"
          />
          {{ tab.label }}
          <span
            v-if="tab.key === 'members'"
            class="ml-auto text-[10px] px-1.5 py-0.5 rounded-full font-medium"
            :class="memberCount < MIN_MEMBERS
              ? 'bg-destructive/10 text-destructive'
              : 'bg-emerald-100 text-emerald-700'"
          >
            {{ memberCount }}
          </span>
        </button>
      </nav>

      <!-- コンテンツ -->
      <div class="flex-1 overflow-y-auto px-8 py-6">

        <!-- 法人情報 -->
        <div v-show="activeTab === 'basic'" class="max-w-4xl space-y-4">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">
              法人名 <span class="text-destructive">*</span>
              <span class="text-[10px] text-muted-foreground/60 ml-0.5">name</span>
            </Label>
            <Input v-model="form.organization.name" placeholder="例：医療法人社団 山田会" maxlength="100" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <Label class="text-xs text-muted-foreground">
                病院名 <span class="text-[10px] text-muted-foreground/60 ml-0.5">abbr</span>
              </Label>
              <Input v-model="form.organization.abbr" placeholder="例：山田内科クリニック" maxlength="100" />
            </div>
            <div class="space-y-1">
              <Label class="text-xs text-muted-foreground">
                WebサイトURL <span class="text-[10px] text-muted-foreground/60 ml-0.5">url</span>
              </Label>
              <Input v-model="form.organization.url" type="url" placeholder="https://example-clinic.jp" maxlength="255" />
            </div>
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">代表者情報</Label>
            <div class="grid grid-cols-3 gap-4">
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">
                  役職 <span class="text-[10px] text-muted-foreground/60 ml-0.5">rep_position</span>
                </Label>
                <Input v-model="form.organization.rep_position" placeholder="例：理事長・院長" maxlength="50" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">
                  姓 <span class="text-[10px] text-muted-foreground/60 ml-0.5">rep_last_name</span><span class="text-destructive">*</span>
                </Label>
                <Input v-model="form.organization.rep_last_name" placeholder="例：山田" maxlength="100" />
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground">
                  名 <span class="text-[10px] text-muted-foreground/60 ml-0.5">rep_first_name</span><span class="text-destructive">*</span>
                </Label>
                <Input v-model="form.organization.rep_first_name" placeholder="例：太郎" maxlength="100" />
              </div>
            </div>
          </div>
        </div>

        <!-- 契約情報 -->
        <div v-show="activeTab === 'contract'" class="max-w-4xl space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <div class="space-y-1">
              <Label class="text-xs text-muted-foreground">
                契約No. <span class="text-[10px] text-muted-foreground/60 ml-0.5">contract_no</span>
              </Label>
              <Input v-model="form.organization.contract_no" type="number" placeholder="自動採番または手動入力" :min="0" />
            </div>
            <div class="space-y-1">
              <Label class="text-xs text-muted-foreground">
                契約日 <span class="text-[10px] text-muted-foreground/60 ml-0.5">contract_date</span>
              </Label>
              <Input v-model="form.organization.contract_date" type="date" />
            </div>
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">
              契約ステータス <span class="text-destructive">*</span>
              <span class="text-[10px] text-muted-foreground/60 ml-0.5">contract_status</span>
            </Label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="opt in CONTRACT_STATUS_OPTIONS"
                :key="opt.value"
                type="button"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm transition-all"
                :class="form.organization.contract_status === opt.value
                  ? opt.activeClass
                  : 'border-border bg-background text-muted-foreground hover:bg-muted'"
                @click="form.organization.contract_status = opt.value"
              >
                <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="opt.bgClass" />
                {{ opt.label }}
              </button>
            </div>
          </div>
          <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">
              支払い方法
              <span class="text-[10px] text-muted-foreground/60 ml-0.5">payment_method</span>
            </Label>
            <div class="flex gap-2">
              <button
                v-for="opt in PAYMENT_METHOD_OPTIONS"
                :key="opt.value"
                type="button"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm transition-all"
                :class="form.organization.payment_method === opt.value
                  ? 'border-blue-500 bg-blue-50 text-blue-800'
                  : 'border-border bg-background text-muted-foreground hover:bg-muted'"
                @click="form.organization.payment_method = opt.value"
              >
                {{ opt.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- 住所情報 -->
        <div v-show="activeTab === 'address'" class="max-w-4xl">
          <AddressForm
            :location-address="form.location_address"
            :shipping-address="form.shipping_address"
            :billing-address="form.billing_address"
            @copy="copyOrgAddress"
          />
        </div>

        <!-- 先生登録 -->
        <div v-show="activeTab === 'members'" class="max-w-4xl space-y-3">
          <MemberCard
            v-for="(member, i) in form.members"
            :key="i"
            :member="member"
            :index="i"
            :member-index="i + 1"
            @remove="removeMember(i)"
            @copy-address="(from, to) => copyMemberAddress(i, from, to)"
          />

          <p
            class="text-xs text-center"
            :class="memberCount < MIN_MEMBERS ? 'text-destructive' : 'text-muted-foreground'"
          >
            <template v-if="memberCount < MIN_MEMBERS">
              あと {{ MIN_MEMBERS - memberCount }} 名追加してください（最低 {{ MIN_MEMBERS }} 名必要）
            </template>
            <template v-else>
              現在 {{ memberCount }} 名登録予定
            </template>
          </p>

          <Button
            type="button" variant="outline"
            class="w-full border-dashed text-muted-foreground hover:text-emerald-600 hover:border-emerald-500"
            @click="addMember"
          >
            <Plus class="w-4 h-4 mr-1" /> 先生を追加する
          </Button>
        </div>

      </div>
    </div>
  </div>
</template>