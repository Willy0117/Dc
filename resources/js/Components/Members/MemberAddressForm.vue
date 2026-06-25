<script setup lang="ts">
import { ref, toRef } from 'vue'
import { Copy } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select'
import type { MemberAddress, MemberAddressType } from '@/types'
import { PREFECTURES } from '@/composables/useOrganizationForm'
import { useZipcode } from '@/composables/useZipcode'

const props = defineProps<{
  addresses: MemberAddress[]
}>()

const emit = defineEmits<{
  (e: 'copy', from: MemberAddressType, to: MemberAddressType): void
}>()

const activeTab = ref<MemberAddressType>(1)

const tabs: { type: MemberAddressType; label: string }[] = [
  { type: 1, label: '自宅' },
  { type: 2, label: '送付先' },
]

function addressOf(type: MemberAddressType) {
  return props.addresses.find(a => a.type === type)!
}

// 自宅の郵便番号連動
const home = addressOf(1)
useZipcode(toRef(home, 'postal_code'), {
  prefecture: toRef(home, 'address1'),
  address1:   toRef(home, 'address2'),
  address2:   toRef(home, 'address3'),
})

// 送付先の郵便番号連動
const shipping = addressOf(2)
useZipcode(toRef(shipping, 'postal_code'), {
  prefecture: toRef(shipping, 'address1'),
  address1:   toRef(shipping, 'address2'),
  address2:   toRef(shipping, 'address3'),
})
</script>

<template>
  <div class="rounded-lg bg-muted/50 p-3 space-y-3">
    <p class="text-xs font-medium text-muted-foreground">
      住所情報
      <span class="text-[10px] text-muted-foreground/60 ml-1">member_addresses</span>
    </p>

    <div class="flex rounded-md border overflow-hidden">
      <button
        v-for="tab in tabs"
        :key="tab.type"
        type="button"
        class="flex-1 py-1.5 text-xs font-medium transition-colors"
        :class="activeTab === tab.type
          ? 'bg-emerald-600 text-white'
          : 'bg-background text-muted-foreground hover:bg-muted'"
        @click="activeTab = tab.type"
      >
        {{ tab.label }}
      </button>
    </div>

    <template v-for="tab in tabs" :key="tab.type">
      <div v-show="activeTab === tab.type" class="space-y-3">

        <Button
          v-if="tab.type === 2"
          type="button" variant="outline" size="sm"
          class="text-emerald-600 border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
          @click="emit('copy', 1, 2)"
        >
          <Copy class="w-3 h-3 mr-1" /> 自宅からコピー
        </Button>

        <div class="grid grid-cols-[160px_1fr] gap-3">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">
              郵便番号 <span class="text-[10px] text-muted-foreground/60">postal_code</span>
            </Label>
            <Input
              v-model="addressOf(tab.type).postal_code"
              placeholder="000-0000"
              maxlength="20"
            />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">
              都道府県 <span class="text-[10px] text-muted-foreground/60">address1</span>
            </Label>
            <Select v-model="addressOf(tab.type).address1">
              <SelectTrigger><SelectValue placeholder="選択" /></SelectTrigger>
              <SelectContent>
                <SelectItem v-for="pref in PREFECTURES" :key="pref" :value="pref">{{ pref }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            市区町村・番地 <span class="text-[10px] text-muted-foreground/60">address2</span>
          </Label>
          <Input v-model="addressOf(tab.type).address2" placeholder="例：新宿区西新宿1-1-1" maxlength="255" />
        </div>

        <div class="space-y-1">
          <Label class="text-xs text-muted-foreground">
            ビル名・部屋番号 <span class="text-[10px] text-muted-foreground/60">address3</span>
          </Label>
          <Input v-model="addressOf(tab.type).address3" placeholder="例：山田ビル 3F" maxlength="255" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">
              電話番号 <span class="text-[10px] text-muted-foreground/60">tel</span>
            </Label>
            <Input v-model="addressOf(tab.type).tel" type="tel" placeholder="03-0000-0000" maxlength="30" />
          </div>
          <div class="space-y-1">
            <Label class="text-xs text-muted-foreground">
              FAX番号 <span class="text-[10px] text-muted-foreground/60">fax</span>
            </Label>
            <Input v-model="addressOf(tab.type).fax" type="tel" placeholder="03-0000-0001" maxlength="30" />
          </div>
        </div>

      </div>
    </template>
  </div>
</template>