<script setup>
import { reactive } from 'vue'
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
  modelValue: Object
})

const form = reactive(props.modelValue)

</script>

<template>
  <div class="grid grid-cols-6 gap-6">

    <!-- 氏名 -->
    <div class="col-span-6 grid grid-cols-6 gap-4">
      <div class="col-span-6 sm:col-span-3">
        <InputLabel value="姓" />
        <TextInput v-model="form.last_name" type="text" class="input-field" />
        <InputError :message="form.errors?.last_name" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <InputLabel value="名" />
        <TextInput v-model="form.first_name" type="text" class="input-field" />
        <InputError :message="form.errors?.first_name" />
      </div>
    </div>

    <!-- かな -->
    <div class="col-span-6 grid grid-cols-6 gap-4">
      <div class="col-span-6 sm:col-span-3">
        <InputLabel value="せい" />
        <TextInput v-model="form.last_name_kana" type="text" class="input-field" />
        <InputError :message="form.errors?.last_name_kana" />
      </div>

      <div class="col-span-6 sm:col-span-3">
        <InputLabel value="めい" />
        <TextInput v-model="form.first_name_kana" type="text" class="input-field" />
        <InputError :message="form.errors?.first_name_kana" />
      </div>
    </div>

    <!-- 性別 -->
    <div class="col-span-6 sm:col-span-3">
      <InputLabel value="性別" />
      <div class="mt-2 flex gap-6">
        <label class="flex items-center gap-2">
          <input type="radio" v-model="form.gender" value="male">
          <span>男</span>
        </label>
        <label class="flex items-center gap-2">
          <input type="radio" v-model="form.gender" value="female">
          <span>女</span>
        </label>
        <label class="flex items-center gap-2">
          <input type="radio" v-model="form.gender" value="other">
          <span>その他</span>
        </label>
      </div>
      <InputError :message="form.errors?.gender" />
    </div>

    <!-- 生年月日 -->
    <div class="col-span-6 sm:col-span-3">
      <InputLabel value="生年月日" />
      <TextInput v-model="form.birthday" type="date" class="input-field" />
      <InputError :message="form.errors?.birthday" />
    </div>

    <!-- ログインメール -->
    <div class="col-span-6 sm:col-span-3">
      <InputLabel value="メール（ログインID）" />
      <TextInput v-model="form.email" type="email" class="input-field" />
      <InputError :message="form.errors?.email" />
    </div>

    <!-- 個人メール -->
    <div class="col-span-6 sm:col-span-3">
      <InputLabel value="個人メール" />
      <TextInput v-model="form.personal_email" type="email" class="input-field" />
      <InputError :message="form.errors?.personal_email" />
    </div>

    <!-- 会員状態 -->
    <div class="col-span-6 sm:col-span-2">
      <InputLabel value="会員状態" />
      <select v-model="form.member_status" class="input-field">
        <option value="active">在籍</option>
        <option value="inactive">休会</option>
        <option value="withdrawn">退会</option>
      </select>
      <InputError :message="form.errors?.member_status" />
    </div>

    <div class="col-span-6 sm:col-span-2">
      <InputLabel value="会員種別" />
      <select v-model="form.member_type" class="input-field">
        <option value="">選択してください</option>
        <option value="doctor">正会員（医師）</option>
      </select>
    </div>
    <!-- 入会日 -->
    <div class="col-span-6 sm:col-span-2">
      <InputLabel value="入会日" />
      <TextInput v-model="form.join_date" type="date" class="input-field" />
    </div>

    <!-- 退会日 -->
    <div class="col-span-6 sm:col-span-2">
      <InputLabel value="退会日" />
      <TextInput v-model="form.leave_date" type="date" class="input-field" />
    </div>

  </div>
</template>