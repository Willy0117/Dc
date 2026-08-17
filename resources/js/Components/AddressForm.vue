<script setup lang="ts">
import { ref } from 'vue'
import { Copy } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import AddressFields from '@/Components/AddressFields.vue'
import type { OrganizationAddress } from '@/types'

defineProps<{
  locationAddress: OrganizationAddress
  shippingAddress: OrganizationAddress
  billingAddress: OrganizationAddress
  errors?: Record<string, string>
}>()

const emit = defineEmits<{
  (e: 'copy', to: 'shipping_address' | 'billing_address'): void
}>()

const activeTab = ref<'location' | 'shipping' | 'billing'>('location')

const tabs = [
  { key: 'location' as const, label: '所在地' },
  { key: 'shipping' as const, label: '郵送先' },
  { key: 'billing'  as const, label: '請求先' },
]
</script>

<template>
  <div class="space-y-4">
    <div class="flex rounded-lg border overflow-hidden">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        type="button"
        class="flex-1 py-2 text-sm font-medium transition-colors"
        :class="activeTab === tab.key
          ? 'bg-[#0C447C] text-white'
          : 'bg-muted text-muted-foreground hover:bg-background'"
        @click="activeTab = tab.key"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-show="activeTab === 'location'">
      <AddressFields
        :address="locationAddress"
        :email-required="true"
        :email-error="errors?.['location_address.email']"
      />
    </div>

    <div v-show="activeTab === 'shipping'" class="space-y-3">
      <Button type="button" variant="outline" size="sm"
        class="text-emerald-600 border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
        @click="emit('copy', 'shipping_address')"
      >
        <Copy class="w-3 h-3 mr-1" /> 所在地からコピー
      </Button>
      <AddressFields
        :address="shippingAddress"
        :email-error="errors?.['shipping_address.email']"
      />
    </div>

    <div v-show="activeTab === 'billing'" class="space-y-3">
      <Button type="button" variant="outline" size="sm"
        class="text-emerald-600 border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
        @click="emit('copy', 'billing_address')"
      >
        <Copy class="w-3 h-3 mr-1" /> 所在地からコピー
      </Button>
      <AddressFields
        :address="billingAddress"
        :email-error="errors?.['billing_address.email']"
      />
    </div>
  </div>
</template>