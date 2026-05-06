<template>
  <div>
    <h1 class="text-xl font-bold mb-4">会社情報 登録</h1>
    <form @submit.prevent="submit">
      <div class="mb-2">
        <input v-model="form.name" type="text" placeholder="会社名" class="input w-full" />
      </div>
      <div class="mb-2">
        <input v-model="form.address" type="text" placeholder="住所" class="input w-full" />
      </div>
      <div class="mb-2">
        <input v-model="form.phone" type="text" placeholder="電話番号" class="input w-full" />
      </div>
      <div class="mb-2">
        <input v-model="form.fax" type="text" placeholder="FAX" class="input w-full" />
      </div>
      <div class="mb-2">
        <input v-model="form.email" type="email" placeholder="メールアドレス" class="input w-full" />
      </div>
      <div class="mb-4">
        <label><input type="checkbox" v-model="form.is_active" /> 有効</label>
      </div>
      <button type="submit" class="btn btn-primary">登録</button>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  address: '',
  phone: '',
  fax: '',
  email: '',
  is_active: true,
})

const submit = () => {
  form.post('/admin/saas')
}
</script>
