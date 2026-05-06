<template>
  <AppLayout>
    <template #header>
      {{ organization ? t('edit_organization') : t('create_organization') }}
    </template>

    <div class="p-6 bg-white rounded shadow">
      <form @submit.prevent="submitForm" class="space-y-8">
        <div class="mb-4 grid grid-cols-2 gap-x-1 gap-y-3 items-start">
          <div class="flex-1">
            <InputLabel for="member_id" :value="t('member')" />
            <Autocomplete
              v-model="form.member_id"
              :placeholder="t('member')"
              apiUrl="/api/members/search"
              :initialItem="{
                id: form.member_id,
                label: form.member_name,
              }"
              class="mt-1 block w-full"
            />
            <InputError :message="form.errors.member_id" class="mt-2" />
          </div>
          <div class="flex-1">
          </div>
        </div>
        <div class="mb-4 grid grid-cols-2 gap-x-1 gap-y-3 items-start">
          <div class="flex-1">
              <InputLabel :value="t('registers.company_name')" /> 
              <!-- 会社名 -->
              <TextInput
                v-model="form.name"
                class="w-full"
                placeholder="〇〇商事"
              />
              <InputError :message="form.errors.name" class="mt-2" />
          </div>
          <div class="flex-1">
              <InputLabel :value="t('registers.abbr')" /> 
              <!-- 会社名 -->
              <TextInput
                v-model="form.abbr"
                class="w-full"
                placeholder="〇〇支店、営業所"
              />
              <InputError :message="form.errors.abbr" class="mt-2" />
          </div>
        </div>        
 
        <div>
        <div>
          <InputLabel :value="t('registers.zip_code')" />
          <TextInput
            v-model="form.postal_code"
            placeholder="000-0000"
            maxlength="8"
            @input="onAddressZipInput"
            @keydown.enter.prevent
          />
          <InputError :message="form.errors.postal_code" class="mt-2" />
          <ul v-if="candidates.length > 1" class="border rounded bg-white">
            <li
              v-for="candidate in candidates"
              :key="candidate.label"
              class="p-2 hover:bg-gray-100 cursor-pointer"
              @click="selectCandidate(candidate, 'corp.address1')"
            >
              {{ candidate.label }}
            </li>
          </ul>
          <InputError :message="form.errors.postal_code" class="mt-2" />
        </div>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end">
            <div class="flex-1">
              <InputLabel :value="t('registers.address1')" />
              <TextInput v-model="form.address1"
                class="w-full"
              />
              <InputError :message="form.errors.address1" class="mt-2" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address2')" />
              <TextInput v-model="form.address2"
                class="w-full" />
              <InputError :message="form.errors.address2" class="mt-2" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address3')" />
              <TextInput v-model="form.address3" class="w-full" />
            </div>
          </div>

          <div class="mb-4 grid grid-cols-3 gap-x-1 gap-y-3 items-start">
            <div class="w-full">
              <InputLabel :value="t('registers.tel')" />
              <TextInput
                v-model="form.tel"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'tel', e)"
                placeholder="03-1234-5678"
              />
              <p v-if="form.tel"
                class="text-xs text-gray-500 mt-1">
                電話番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>

            <div class="w-full">
              <InputLabel :value="t('registers.fax')" />
              <TextInput
                v-model="form.fax"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'fax', e)"
                placeholder="03-1234-5678"
              />
              <p v-if="form.fax"
                class="text-xs text-gray-500 mt-1">
                FAX番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>
            <div class="w-full">
              <InputLabel :value="t('registers.mobile')" />
              <TextInput
                v-model="form.mobile"
                class="w-full"
                placeholder="090-xxxx-xxxx"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'mobile', e)"
              />
              <p v-if="form.mobile"
                class="text-xs text-gray-500 mt-1">
                携帯電話は 090-1234-5678 の形式で入力してください
              </p>
            </div>
          </div>
        </div>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
          <div class="flex-1">
            <InputLabel>
              {{ t('registers.email') }}
              <span v-if="form.is_agent" class="text-red-500 ml-1">*</span>
            </InputLabel>

            <TextInput
              v-model="form.email" class="w-full"
            />

            <p
              v-if="form.errors.email"
              class="text-red-500 text-sm mt-1"
            >
              {{ form.errors.email }}
            </p>
          </div>
          <div class="flex-[2]">
            <div>
              <InputLabel :value="t('registers.staff')" />
              <div class="flex gap-2">
                <TextInput v-model="form.last_name"
                    class="w-full" />
                <TextInput v-model="form.first_name"
                  class="w-full" />
              </div>
            </div>
          </div>
        </div>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
          <div class="flex-1">
            <InputLabel>{{ t('applications.text_color') }}</InputLabel>
            <div class="inline-flex rounded-md shadow-sm">
              <!-- 禁止ボタン -->
              <button
                type="button"
                @click="form.allow_text_color = 0"
                :class="form.allow_text_color === 0 ? 'bg-white border-indigo-600 text-indigo-600 z-10' : 'bg-gray-50 border-gray-300 text-gray-500'"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium border rounded-l-md justify-center transition"
              >
                {{ t('disallow') }}
              </button>
              <!-- 有効ボタン -->
              <button
                type="button"
                @click="form.allow_text_color = 1"
                :class="form.allow_text_color === 1 ? 'bg-white border-indigo-600 text-indigo-600 z-10' : 'bg-gray-50 border-gray-300 text-gray-500'"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium border -ml-px rounded-r-md justify-center transition"
              >
                {{ t('allow') }}
              </button>
            </div>
          </div>
          <div class="flex-1">
            <InputLabel>{{ t('applications.bg_color') }}</InputLabel>
            <div class="inline-flex rounded-md shadow-sm">
              <!-- 禁止ボタン -->
              <button
                type="button"
                @click="form.allow_background_color = 0"
                :class="form.allow_background_color === 0 ? 'bg-white border-indigo-600 text-indigo-600 z-10' : 'bg-gray-50 border-gray-300 text-gray-500'"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium border rounded-l-md justify-center transition"
              >
                {{ t('disallow') }}
              </button>
              <!-- 有効ボタン -->
              <button
                type="button"
                @click="form.allow_background_color = 1"
                :class="form.allow_background_color === 1 ? 'bg-white border-indigo-600 text-indigo-600 z-10' : 'bg-gray-50 border-gray-300 text-gray-500'"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium border -ml-px rounded-r-md justify-center transition"
              >
                {{ t('allow') }}
              </button>
            </div>
          </div>
          <div class="flex-1"></div>
        </div>
        <div class="flex justify-end">
          <PrimaryButton :disabled="form.processing">
            {{ props.organization ? t('update') : t('create') }}
          </PrimaryButton>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, computed, toRef } from 'vue'
import { Link, router, useForm,usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import Autocomplete from '@/Components/OpenCartAutocomplete.vue'

import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios'
import { useZipcode } from '@/composables/useZipcode'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const test = ref('1')
const page = usePage()

console.log(page.props) // ← ここで form が見える

const props = defineProps({
  organization: Object,
  filter: Object,
})


const form = useForm({
  id:props.organization?.id ?? '',
  name: props.organization?.name ?? '',
  postal_code: props.organization?.postal_code ?? '',
  address1: props.organization?.address1 ?? '',
  address2: props.organization?.address2 ?? '',
  address3: props.organization?.address3 ?? '',
  tel: props.organization?.tel ?? '',
  fax: props.organization?.fax ?? '',
  mobile: props.organization?.mobile ?? '',
  email: props.organization?.email ?? '',
  cc_email: props.organization?.cc_email ?? '',
  bcc_email: props.organization?.bcc_email ?? '',
  last_name: props.organization?.last_name ?? '',
  first_name: props.organization?.first_name ?? '',
  member_id:props.organization?.member_id ?? '',
  member_name:props.organization?.member_name ?? '',
  allow_text_color:props.organization?.allow_text_color ?? 1,
  allow_background_color:props.organization?.allow_background_color ?? 1,
});


// 送信処理
const submitForm = () => {
  console.log('送信データ:', form);
  if (form?.id) {
    form.put(route('admin.organizations.update', props.organization.id))
  } else {
    form.post(route('admin.organizations.store'))
  }
}

const normalizePhone = (value) => {
  if (!value) return ''

  // 全角数字 → 半角
  value = value.replace(/[０-９]/g, s =>
    String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
  )

  // 全角ハイフン → 半角
  value = value.replace(/[ー－―]/g, '-')

  // 数字とハイフン以外を除去
  return value.replace(/[^0-9-]/g, '')
}

/**
 * @param {'corp'|'mail'} target
 * @param {'tel'|'fax'|'mobile'} field
 */
const onPhoneInput = (target, field, e) => {
  form[target][field] = normalizePhone(e.target.value)
}



const normalizeKana = (value) => {
  if (!value) return ''

  // ひらがな → カタカナ
  value = value.replace(/[\u3041-\u3096]/g, s =>
    String.fromCharCode(s.charCodeAt(0) + 0x60)
  )

  // 全角カタカナ・長音・全角スペースのみ
  return value.replace(/[^\u30A0-\u30FFー　]/g, '')
}

//〒番号関係
const candidates = ref([])
/*
 * 郵便番号正規化
 * ・全角数字 → 半角
 * ・全角ハイフン → 半角
 * ・数字とハイフン以外を除去
 */
const normalizeZip = (value) => {
  if (!value) return ''

  // 全角数字 → 半角
  value = value.replace(/[０-９]/g, s =>
    String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
  )

  // 全角ハイフン → 半角
  value = value.replace(/[ー－―]/g, '-')

  // 数字とハイフン以外を除去
  return value.replace(/[^0-9-]/g, '')
}
/**
 * 住所候補選択
 * @param {Object} candidate
 * @param {String} field  formのキー名
 */
function selectCandidate(candidate, field) {
  if (!candidate || !field) return
  if (!(field in form)) return

  form[field] = candidate.label
  candidates.value = []
}

const onAddressZipInput = (e) => {
  form.postal_code = normalizeZip(e.target.value)
}

useZipcode(
  toRef(form, 'postal_code'),
  toRef(form, 'address1')
)
</script>

