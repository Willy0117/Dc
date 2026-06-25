<script setup lang="ts">
import { toRef } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select'
import type { MemberAddressItem } from '@/types'
import { PREFECTURES } from '@/composables/useOrganizationForm'
import { useZipcode } from '@/composables/useZipcode'

const props = defineProps<{
  address: MemberAddressItem
}>()

const zipRef = toRef(props.address, 'postal_code')

useZipcode(zipRef, {
  prefecture: toRef(props.address, 'address1'),
  address1:   toRef(props.address, 'address2'),
  address2:   toRef(props.address, 'address3'),
})
</script>

<template>
  <div class="space-y-3">
    <div class="grid grid-cols-[160px_1fr] gap-3">
      <div class="space-y-1">
        <Label class="text-xs text-muted-foreground">
          郵便番号 <span class="text-[10px] text-muted-foreground/60">postal_code</span>
        </Label>
        <Input
          v-model="address.postal_code"
          placeholder="000-0000"
          maxlength="8"
          @input="address.postal_code = ($event.target as HTMLInputElement).value
            .replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
            .replace(/[－ー−‐]/g, '-')"
        />
      </div>
      <div class="space-y-1">
        <Label class="text-xs text-muted-foreground">
          都道府県 <span class="text-[10px] text-muted-foreground/60">address1</span>
        </Label>
        <Select v-model="address.address1">
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
      <Input v-model="address.address2" placeholder="例：新宿区西新宿1-1-1" maxlength="255" />
    </div>

    <div class="space-y-1">
      <Label class="text-xs text-muted-foreground">
        ビル名・部屋番号 <span class="text-[10px] text-muted-foreground/60">address3</span>
      </Label>
      <Input v-model="address.address3" placeholder="例：山田ビル 3F" maxlength="255" />
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div class="space-y-1">
        <Label class="text-xs text-muted-foreground">
          電話番号 <span class="text-[10px] text-muted-foreground/60">tel</span>
        </Label>
        <Input v-model="address.tel" type="tel" placeholder="03-0000-0000" maxlength="30" />
      </div>
      <div class="space-y-1">
        <Label class="text-xs text-muted-foreground">
          FAX番号 <span class="text-[10px] text-muted-foreground/60">fax</span>
        </Label>
        <Input v-model="address.fax" type="tel" placeholder="03-0000-0001" maxlength="30" />
      </div>
    </div>
  </div>
</template>
