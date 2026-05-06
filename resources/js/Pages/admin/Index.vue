<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import axios from 'axios'
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentPlusIcon} from '@heroicons/vue/24/outline'

const { t } = useI18n()

const page = usePage()

const props = defineProps({
  fees: Object
})
    
</script>

<template>
  <AppLayout>
    <template #header>{{ t('annual_fees.list') }}</template>
  <div class="p-6">
    <!-- テーブル -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">
      <table class="min-w-full text-sm text-left">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2">{{ t('annual_fees.fiscal_year') }}</th>
            <th class="px-4 py-2 text-right">{{ t('annual_fees.annual_fee') }}</th>
            <th class="px-4 py-2 text-right">{{ t('annual_fees.renewal_fee') }}</th>
            <th class="px-4 py-2 text-right">{{ t('annual_fees.payment_amount') }}</th>
            <th class="px-4 py-2 text-right">{{ t('annual_fees.balance') }}</th>
            <th class="px-4 py-2">{{ t('annual_fees.payment_date') }}</th>
            <th class="px-4 py-2">{{ t('annual_fees.status') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="fee in props?.fees"
            :key="fee.id"
            class="odd:bg-white even:bg-gray-100 border-t hover:bg-gray-50"
          >
            <!-- 年度 -->
            <td class="px-4 py-2">
              {{ fee.fiscal_year }} {{ t('annual_fees.fiscal_year') }}
            </td>

            <!-- 年会費 -->
            <td class="px-4 py-2 text-right">
              {{ fee.annual_fee.toLocaleString() }}
            </td>

            <!-- 更新料 -->
            <td class="px-4 py-2 text-right">
              {{ fee.renewal_fee.toLocaleString() }}
            </td>

            <!-- 入金額 -->
            <td class="px-4 py-2 text-right">
              {{ fee.payment_amount.toLocaleString() }}
            </td>

            <!-- 残額 -->
            <td class="px-4 py-2 text-right font-semibold">
              {{
                (fee.annual_fee + fee.renewal_fee - fee.payment_amount)
                  .toLocaleString()
              }}
            </td>

            <!-- 入金日 -->
            <td class="px-4 py-2">
              {{ fee.payment_date ?? '-' }}
            </td>

            <!-- ステータス -->
            <td class="px-4 py-2">
              <span
                class="px-2 py-1 rounded text-xs font-semibold"
                :class="{
                  'bg-green-100 text-green-700': fee.status === 'paid',
                  'bg-yellow-100 text-yellow-700': fee.status === 'partial',
                  'bg-red-100 text-red-700': fee.status === 'unpaid'
                }"
              >
                {{ fee.status }}
              </span>
            </td>
          </tr>

          <!-- データなし -->
          <tr v-if="fees.length === 0">
            <td colspan="7" class="text-center py-6 text-gray-400">
              データがありません
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  </AppLayout>
</template>