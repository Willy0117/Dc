<script setup>
import { ref, onMounted, watch, nextTick, computed, toRef, reactive } from 'vue'
import { Link, router, useForm,usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

import axios from 'axios'
import { useZipcode } from '@/composables/useZipcode'

import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  modelValue: Object,
  errors: Object // 👈 追加
})

const emit = defineEmits(['update:modelValue'])

const defaults = {
  home: {
    postal_code: '',
    prefecture: '',
    address1: '',
    address2: '',
    tel: '',
    mobile: '',
    fax: ''
  },
  work: {
    postal_code: '',
    prefecture: '',
    address1: '',
    address2: '',
    tel: '',
    fax: ''
  },
  postal: {
    useWork: false
  }
}

const form = reactive({
  ...defaults,
  ...props.modelValue,
})

watch(form, () => {
  emit('update:modelValue', form)
}, { deep: true })


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
 */
const onPhoneInput = (field, e) => {
  form[field] = normalizePhone(e.target.value)
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

<template>
  <div class="space-y-8">
        <div>
          <InputLabel :value="t('members.zip_code')" />
          <TextInput
            v-model="form.postal_code"
            placeholder="000-0000"
            maxlength="8"
            @input="onAddressZipInput"
            @keydown.enter.prevent
          />

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
          <InputError :message="form.errors.postal_code" />
        </div>

        <div class="col-span-6  sm:col-span-6 grid grid-cols-6 gap-4 sm:items-start">
            <div class="flex-1">
              <InputLabel :value="t('members.address1')" />
              <TextInput v-model="form.address1"
                class＝”input-field”
              />
              <InputError :message="form.errors.address1" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('members.address2')" />
              <TextInput v-model="form.address2"
                class＝”input-field” />
              <InputError :message="form.errors.address2" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('members.address3')" />
              <TextInput v-model="form.address3" class＝”input-field” />
            </div>
        </div>
        <div class="mb-4 grid grid-cols-4 gap-x-1 gap-y-3 sm:flex-row sm:items-start">
            <div>
              <InputLabel :value="t('members.tel')" />
              <TextInput
                v-model="form.tel"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'tel', e)"
                placeholder="03-1234-5678"
              />
              <InputError :message="form.errors.tel" />
              <p v-if="form.tel"
                class="text-xs text-gray-500 mt-1">
                電話番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>

            <div>
              <InputLabel :value="t('members.fax')" />
              <TextInput
                v-model="form.fax"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'fax', e)"
                placeholder="03-1234-5678"
              />
              <p v-if="form.fax" class="text-xs text-gray-500 mt-1">
                FAX番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>
            <div>
              <InputLabel :value="t('members.mobile')" />
              <TextInput
                v-model="form.mobile"
                class＝”input-field”
                placeholder="090-xxxx-xxxx"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'mobile', e)"
              />
              <p v-if="form.mobile"
                class="text-xs text-gray-500 mt-1">
                携帯電話は 090-1234-5678 の形式で入力してください
              </p>
            </div>
            <div v-if="form.is_agent">
              <InputLabel>{{ t('members.email') }}</InputLabel>

              <TextInput v-model="form.email" class＝”input-field” />
              <InputError :value="form.errors.email" />
            </div>
        </div>
    <!-- 🏠 自宅 -->
    <div>
      <h3 class="text-lg font-semibold mb-4">自宅住所</h3>

      <div class="grid grid-cols-6 gap-4">

        <div class="col-span-2">
          <InputLabel value="郵便番号" />
          <TextInput v-model="form.home.postal_code" class="input-field" />
          <InputError :message="errors?.['address.home.postal_code']" />
        </div>

        <div class="col-span-2">
          <InputLabel value="都道府県" />
          <TextInput v-model="form.home.prefecture" class="input-field" />
          <InputError :message="errors?.['address.home.prefecture']" />
        </div>

        <div class="col-span-6">
          <InputLabel value="住所" />
          <TextInput v-model="form.home.address1" class="input-field" />
          <InputError :message="errors?.['address.home.address1']" />
        </div>

        <div class="col-span-6">
          <InputLabel value="建物名など" />
          <TextInput v-model="form.home.address2" class="input-field" />
          <InputError :message="errors?.['address.home.address2']" />
        </div>

        <div class="col-span-2">
          <InputLabel value="電話番号" />
          <TextInput v-model="form.home.tel" class="input-field" />
          <InputError :message="errors?.['address.home.tel']" />
        </div>

        <div class="col-span-2">
          <InputLabel value="携帯" />
          <TextInput v-model="form.home.mobile" class="input-field" />
          <InputError :message="errors?.['address.home.mobile']" />
        </div>

        <div class="col-span-2">
          <InputLabel value="FAX" />
          <TextInput v-model="form.home.fax" class="input-field" />
          <InputError :message="errors?.['address.home.fax']" />
        </div>

      </div>
    </div>

    <!-- 🏢 所属 -->
    <div>
      <h3 class="text-lg font-semibold mb-4">所属先住所</h3>

      <div class="grid grid-cols-6 gap-4">

        <div class="col-span-2">
          <InputLabel value="郵便番号" />
          <TextInput v-model="form.work.postal_code" class="input-field" />
          <InputError :message="errors?.['address.work.postal_code']" />
        </div>

        <div class="col-span-2">
          <InputLabel value="都道府県" />
          <TextInput v-model="form.work.prefecture" class="input-field" />
          <InputError :message="errors?.['address.work.prefecture']" />
        </div>

        <div class="col-span-6">
          <InputLabel value="住所" />
          <TextInput v-model="form.work.address1" class="input-field" />
          <InputError :message="errors?.['address.work.address1']" />
        </div>

        <div class="col-span-6">
          <InputLabel value="建物名など" />
          <TextInput v-model="form.work.address2" class="input-field" />
          <InputError :message="errors?.['address.work.address2']" />
        </div>

        <div class="col-span-3">
          <InputLabel value="電話番号" />
          <TextInput v-model="form.work.tel" class="input-field" />
          <InputError :message="errors?.['address.work.tel']" />
        </div>

        <div class="col-span-3">
          <InputLabel value="FAX" />
          <TextInput v-model="form.work.fax" class="input-field" />
          <InputError :message="errors?.['address.work.fax']" />
        </div>

      </div>
    </div>

    <!-- 送付先 -->
    <div>
      <h3 class="text-lg font-semibold mb-2">郵送物送付先</h3>

      <label class="flex items-center gap-2">
        <input type="checkbox" v-model="form.postal.useWork">
        <span>所属先へ送付する</span>
      </label>
      <InputError :message="errors?.['address.postal.useWork']" />
    </div>

  </div>
</template>