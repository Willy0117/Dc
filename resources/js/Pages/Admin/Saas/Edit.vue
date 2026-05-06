<template>
  <v-container>
    <v-form @submit.prevent="submit" ref="formRef">
      <v-text-field v-model="form.name" label="会社名" required></v-text-field>
      <v-text-field v-model="form.address" label="住所"></v-text-field>
      <v-text-field v-model="form.phone" label="電話番号"></v-text-field>
      <v-text-field v-model="form.fax" label="FAX"></v-text-field>
      <v-text-field v-model="form.email" label="メール" type="email"></v-text-field>
      <v-checkbox v-model="form.is_active" label="有効"></v-checkbox>
      <v-btn type="submit" color="primary">保存</v-btn>
    </v-form>
  </v-container>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  saas: Object
})

const form = useForm({
  name: props?.saas?.name ?? '',
  address: props?.saas?.address ?? '',
  phone: props?.saas?.phone ?? '',
  fax: props?.saas?.fax ?? '',
  email: props?.saas?.email ?? '',
  is_active: props?.saas?.is_active ?? true,
})

const submit = () => {
  if (props?.saas) {
    form.put(`/admin/saas/${props.saas.id}`)
  } else {
    form.post('/admin/saas')
  }
}
</script>
