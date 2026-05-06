<template>
  <AppLayout>
    <template #header>{{ member ? t('members.edit') : t('members.update') }}</template>
    <form @submit.prevent="submit" class="w-full">

      <TabGroup v-model="activeTab" :tabs="tabs" v-slot="{ activeTab }">

        <div v-if="activeTab === 'basic'">
          <MemberForm v-model="form.member" />
        </div>

        <div v-if="activeTab === 'address'">
          <AddressForm v-model="form.member" />
        </div>

        <div v-if="activeTab === 'affiliation'">
          <AddressForm v-model="form.member" />
        </div>

      </TabGroup>
      

        <div class="flex justify-between items-center mt-6">
          
          <!-- 左：キャンセル -->
          <SecondaryButton type="button">
            <Link :href="route('admin.members.index')">
              {{ t('members.cancel') }}
            </Link>
          </SecondaryButton>

          <!-- 右：保存 -->
          <PrimaryButton
            type="button"
            @click="submitForm"
          >
            {{ props.member ? t('update') : t('create') }}
          </PrimaryButton>

        </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, computed, toRef } from 'vue'
import { Link, router, useForm,usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import MemberForm from './Components/MemberForm.vue'
import AddressForm from './Components/AddressForm.vue'
import TabGroup from '@/Components/TabGroup.vue';

import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const page = usePage()

const bankInput = ref(null)

const activeTab = ref('basic')

const tabs = [
  { id: 'basic', label: '基本情報' },
  { id: 'address', label: '住所' },
  { id: 'affiliation', label: '所属' }
]

watch(
  () => page.props.errors,
  e => {
    console.log('Laravel errors:', e)
  },
  { deep: true }
)

const props = defineProps({
  member: Object,

})

const form = useForm({
  member: {
    id: props.member?.id ?? '',
    last_name: props.member?.last_name ?? '',
    first_name: props.member?.first_name ?? '',
    last_name: props.member?.last_name_kana ?? '',
    first_name: props.member?.first_name_kana ?? '',
    gender: props.member?.gender ?? 'male',
    birthdate: props.member?.birthdate,
    email: props.member?.email ?? '',
    member_status: props.member?.member_status,
    member_type: props.member?.member_type ?? '',
    join_date: props.member?.join_date,
    leave_date: props.member?.leave_date,
    personal_email: props.member?.personal_email,
    errors: {}
  },
  postal_code: props.member?.postal_code ?? '',
  address1: props.member?.address1 ?? '',
  address2: props.member?.address2 ?? '',
  address3: props.member?.address3 ?? '',
  tel: props.member?.tel ?? '',
  fax: props.member?.fax ?? '',
  mobile: props.member?.mail?.mobile ?? '',
});

const errors = ref({})

// 送信処理
const submitForm = () => {
  console.log('送信データ:', form);
  if (form.id) {
    form.put(route('admin.members.update', form.id), {
      onError: (e) => console.log(e)
    })
  } else {
    form.post(route('admin.members.store'), {
      onError: (e) => console.log(e)
    })
  }
}

const focusNext = (e) => {

  if (e.isComposing) return

  const form = e.target.form
  const index = Array.prototype.indexOf.call(form, e.target)

  if (index > -1) {
    e.preventDefault()
    form.elements[index + 1]?.focus()
  }
}
</script>
<style>
.input-field {
  @apply w-full rounded-md border border-gray-300 px-3 py-2 text-sm
         shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500;
}
</style>