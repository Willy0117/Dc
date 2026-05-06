<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue';

import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Autocomplete from '@/Components/OpenCartAutocomplete.vue'
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { useI18n } from 'vue-i18n'
import { Inertia } from '@inertiajs/inertia';
import { ref, onMounted } from 'vue';
import dayjs from 'dayjs';

const { t } = useI18n()

const props = defineProps({
  defaultFuneralDatetime: String,
  minFuneralDatetime: String,
  application_date: String,
  delivery_date: String,
  user: Object,
  flash: Object, 
})

const form = useForm({
    application_date: props.application_date,
    delivery_date: props.delivery_date,
    staff_name: '',
    funeral_datetime: props.defaultFuneralDatetime,
    last_name: '',
    first_name: '',
    deceased_furigana: '',
    remarks: '',
    organization_id: '',
    pdf: null,
    gender: '',
});

const submit = () => {
    form.post(route('admin.applications.faxstore'), {
        preserveScroll: true,
        forceFormData: true
    });
};

const updateDeliveryDate = () => {
  let now = dayjs(); // 現在時刻
  let delivery;

  if (now.hour() < 15) {
    delivery = now.add(3, 'hour');
  } else {
    delivery = now.add(1, 'day').hour(12).minute(0);
  }

  // 30分丸め
  const minute = delivery.minute();
  if (minute > 0 && minute <= 30) {
    delivery = delivery.minute(30);
  } else if (minute > 30) {
    delivery = delivery.add(1, 'hour').minute(0);
  }
  delivery = delivery.second(0);

  form.delivery_date = delivery.format('YYYY-MM-DDTHH:mm'); // datetime-local 用
};

onMounted(() => {
  updateDeliveryDate();
  setInterval(updateDeliveryDate, 60 * 1000); // 1分ごとに更新
});

const pdfInput = ref(null)

const triggerFileSelect = () => {
  pdfInput.value.click()
}

const handleFileSelect = (e) => {
  const file = e.target.files[0]
  if (!file) return
  form.pdf = file
}

const handleDrop = (e) => {
  const file = e.dataTransfer.files[0]
  if (!file) return
  form.pdf = file
}


</script>

<template>
  <AppLayout>
  <template #header>{{ t('applications.fax') }}</template>
    <ActionMessage :on="props.flash.success">
        {{ props.flash.success }}
    </ActionMessage>
    <FormSection @submitted="submit">
        <template #form>
            <div class="col-span-6 sm:col-span-2">
                <InputLabel for="delivery_date" :value="t('registers.delivery_date')" />
                <TextInput
                    v-model="form.delivery_date"
                    type="datetime-local"
                    :min="minFuneralDatetime"
                    class="mt-1 block w-full bg-[#ddd5bc]"
                    readonly
                />
            </div>
        
            <!-- 申込基本情報 -->
            <div class="col-span-6 sm:col-span-2">
                <InputLabel for="organization_id" :value="t('applications.organization')" />
                <Autocomplete
                    v-model="form.organization_id"
                    :placeholder="t('applications.organization')"
                    apiUrl="/api/organizations/search"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.organization_id" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-2">
                <InputLabel for="staff_name" :value="t('registers.staff_name')" />
                <TextInput id="staff_name" v-model="form.staff_name" type="text" class="mt-1 block w-full" />
                <InputError :message="form.errors.staff_name" class="mt-2" />
            </div>
            <!-- 氏名 -->
            <div class="col-span-6  sm:col-span-2">
                <InputLabel for="last_name" :value="t('registers.last_name')" />
                    <TextInput
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        :placeholder="t('registers.last_name')"
                        class="w-full"
                    />
                <InputError :message="form.errors.last_name" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-2">
                <InputLabel for="first_name" :value="t('registers.first_name')" />
                    <TextInput
                        id="first_name"
                        v-model="form.first_name"
                        type="text"
                        :placeholder="t('registers.first_name')"
                        class="w-full"
                    />
                <InputError :message="form.errors.first_name" class="mt-2" />
            </div>
            <!-- 性別 -->
            <div class="col-span-6 sm:col-span-1">
                <InputLabel value="性別" />
                <div class="mt-2 flex gap-6">
                    <label class="flex items-center gap-2">
                        <input type="radio" v-model="form.gender" value="男"
                              class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">男</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" v-model="form.gender" value="女"
                              class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">女</span>
                    </label>
                </div>
            </div>
            <div class="col-span-6  sm:col-span-2">
                <InputLabel for="deceased_name" :value="t('registers.furigana')" />
                    <TextInput
                        v-model="form.deceased_furigana"
                        type="text"
                        :placeholder="t('registers.furigana')"
                        class="w-full"
                    />
                <InputError :message="form.errors.deceased_furigana" class="mt-2" />
            </div>
            <!-- PDF アップロード 2点 -->
            <div class="col-span-6">
                <InputLabel for="pdf" :value="t('applications.pdfUploads')" />

                <div
                    @dragover.prevent
                    @dragenter.prevent
                    @drop.prevent="handleDrop"
                    @click="triggerFileSelect"
                    class="border-2 border-dashed border-gray-300 bg-[#ddd5bc] text-center cursor-pointer transition min-h-[160px] flex items-center justify-center"
                >
                    <div v-if="!form.pdf">
                        <p>
                            申込書（PDF）をドラッグ＆ドロップ または クリックして選択
                        </p>
                    </div>

                    <div v-else class="flex flex-col items-center">
                        <svg class="w-10 h-10 text-red-500 mb-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                        </svg>

                        <p class="text-sm">{{ form.pdf.name }}</p>

                        <button
                            type="button"
                            class="text-xs text-red-500 hover:underline mt-1"
                            @click.stop="form.pdf = null"
                        >
                            削除
                        </button>
                    </div>

                    <input
                        type="file"
                        class="hidden"
                        ref="pdfInput"
                        accept="application/pdf"
                        @change="handleFileSelect"
                    />
                </div>

                <InputError :message="form.errors.pdf" />
            </div>           
            <div class="col-span-6">
                <InputLabel for="remarks" :value="t('registers.note')" />
                <textarea id="remarks" v-model="form.remarks" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2"></textarea>
            </div>
            
        </template>

        <template #actions>       
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ t('create') }}
            </PrimaryButton>
        </template>
    </FormSection>
  </AppLayout>    
</template>
