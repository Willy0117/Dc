<template>
  <AppLayout :title="t('organization_edit')">
    <template #header>{{ t('organization_edit') }}</template>

    <div class="max-w-2xl mx-auto py-8 space-y-6">

      <!-- 組織名 autocomplete -->
      <AutoComplete
        v-model="orgNameSearch"
        :label="t('organization_name')"
        placeholder="Type organization name"
        fetch-url="/profile/organizations/autocomplete"
        item-label-key="name"
        @selected="onSelected"
      />

      <!-- 電話番号 autocomplete -->
      <AutoComplete
        v-model="orgPhoneSearch"
        :label="t('contact_phone')"
        placeholder="Type phone number"
        fetch-url="/profile/organizations/autocomplete"
        item-label-key="contact_phone"
        @selected="onSelected"
      />

      <!-- 選択後の補足情報プレビュー -->
      <div v-if="selectedOrg" class="p-2 bg-gray-50 rounded border text-sm text-gray-600 space-y-1">
        <div>{{ t('organization_name') }}: {{ selectedOrg.name }}</div>
        <div>{{ t('contact_phone') }}: {{ selectedOrg.contact_phone }}</div>
        <div>{{ t('billing_name') }}: {{ selectedOrg.billing_name }}</div>
        <div>{{ t('billing_address') }}: {{ selectedOrg.billing_address }}</div>
        <div>{{ t('contact_person') }}: {{ selectedOrg.contact_person }}</div>
        <div>{{ t('contact_email') }}: {{ selectedOrg.contact_email }}</div>
        <div>{{ t('registration_number') }}: {{ selectedOrg.registration_number }}</div>
      </div>

      <!-- 通常フォーム -->
      <div>
        <label class="block mb-1">{{ t('billing_name') }}</label>
        <input v-model="form.billing_name" class="input w-full" />
      </div>

      <div>
        <label class="block mb-1">{{ t('billing_postal') }}</label>
        <input v-model="form.billing_postal" class="input w-full" />
      </div>

      <div>
        <label class="block mb-1">{{ t('billing_address') }}</label>
        <input v-model="form.billing_address" class="input w-full" />
      </div>

      <div>
        <label class="block mb-1">{{ t('contact_person') }}</label>
        <input v-model="form.contact_person" class="input w-full" />
      </div>

      <div>
        <label class="block mb-1">{{ t('contact_email') }}</label>
        <input v-model="form.contact_email" class="input w-full" />
      </div>

      <div>
        <label class="block mb-1">{{ t('registration_number') }}</label>
        <input v-model="form.registration_number" class="input w-full" />
      </div>

      <!-- 保存ボタン -->
      <div class="flex justify-end">
        <button class="btn-primary" @click="submit">{{ t('update') }}</button>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import AutoComplete from '@/Components/Autocomplete.vue'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    organization: Object
})

const selectedOrg = ref(null)
const orgNameSearch = ref(props.organization?.name ?? '')
const orgPhoneSearch = ref(props.organization?.contact_phone ?? '')

const form = useForm({
    organization_id: props.organization?.id ?? null,
    name: props.organization?.name ?? '',
    billing_name: props.organization?.billing_name ?? '',
    billing_postal: props.organization?.billing_postal ?? '',
    billing_address: props.organization?.billing_address ?? '',
    contact_person: props.organization?.contact_person ?? '',
    contact_email: props.organization?.contact_email ?? '',
    contact_phone: props.organization?.contact_phone ?? '',
    registration_number: props.organization?.registration_number ?? '',
})

// Autocomplete 選択時に form を更新
function onSelected(org) {
    console.log('選択された組織:', org) 
    selectedOrg.value = org
    form.organization_id = org.id
    form.name = org.name
    form.contact_phone = org.contact_phone
    form.billing_name = org.billing_name
    form.billing_postal = org.billing_postal
    form.billing_address = org.billing_address
    form.contact_person = org.contact_person
    form.contact_email = org.contact_email
    form.registration_number = org.registration_number

    // 検索用文字列も更新
    orgNameSearch.value = org.name
    orgPhoneSearch.value = org.contact_phone
}

const submit = () => {
    form.put('/profile/organization')
}
</script>





