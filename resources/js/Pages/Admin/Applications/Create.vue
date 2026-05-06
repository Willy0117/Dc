<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue';

import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { useI18n } from 'vue-i18n'
import { Inertia } from '@inertiajs/inertia';
import { ref, onMounted } from 'vue';
import Vue3SignaturePad from 'vue3-signature-pad'
import dayjs from 'dayjs';
import Autocomplete from '@/Components/OpenCartAutocomplete.vue'

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
    text_color: 'brown',
    bg_color: 'none',
    last_name: '',
    first_name: '',
    deceased_furigana: '',
    age_at_death: '', // 満 歳
    gender: '',
    // --- 配偶者・家族情報 ---
    spouse_status: '', // 有・無・死別
    children_count: 0,
    grandchildren_count: 0,
    // --- 喪主様情報 ---
    chief_mourner_name: '', // 喪主様お名前
    relationship_to_deceased: '', // 故人様とのご関係
    // --- その他項目 ---
    traits: [],
    special_notes: '',
    remarks: '',
    canvas: null, // ここに手書き画像を格納 
    organization_id: 0,
});

const canvasPad = ref(null);

const clearPad = () => {
  canvasPad.value?.clearSignature()
}

const traitsOptions = [
    '優しい', '明朗', '温和', '誠実', '思いやり', '面倒見良い', '忍耐強い',
    '親切', '真面目', '努力家', '積極的', '責任感が強い', '世話好き'
];

const colors = ['none','green','pink','blue','orange']

const getImageUrl = (color) => {
  const fileName = color === 'none' ? 'none_thumb.png' : `${color}_thumb.png`;
  return `/images/color/bg-color/${fileName}`;
}

const text_colors = ['brown','green','pink','blue','orange','yellow']

const getTextImageUrl = (color) => {
  return `/images/color/text/${color}_thumb.png`;
}

const submit = () => {
    if (!canvasPad.value.isEmpty()) {
        form.canvas = canvasPad.value.toDataURL(); // PNG base64
    }
    form.post(route('admin.applications.store'), {
        preserveScroll: true,
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


</script>

<template>
  <AppLayout>
  <template #header>{{ t('applications.poem') }}</template>
    <ActionMessage :on="props.flash.success">
        {{ props.flash.success }}
    </ActionMessage>
    <FormSection @submitted="submit">
        <template #form>
            <div class="col-span-6">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6 sm:col-span-3">
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
                <div class="col-span-6 sm:col-span-3">
                    <InputLabel for="funeral" :value="t('registers.funeral_datetime')" />
                    <TextInput
                        v-model="form.funeral_datetime"
                        type="datetime-local"
                        :min="minFuneralDatetime"
                        class="mt-1 block w-full"
                        />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <InputLabel for="organization_id" :value="t('applications.organization')" />
                    <Autocomplete
                        v-model="form.organization_id"
                        :placeholder="t('applications.organization')"
                        apiUrl="/api/organizations/search"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.organization_id" class="mt-2" />
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <InputLabel for="staff_name" :value="t('registers.staff_name')" />
                    <TextInput id="staff_name" v-model="form.staff_name" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.staff_name" class="mt-2" />
                </div>
            </div>
            </div>
            <!-- 喪主様情報 -->
            <div class="col-span-6 sm:col-span-2 border-gray-300">
                <InputLabel for="chief_mourner_name" :value="t('registers.chief_mourner_name')" />
                <TextInput id="chief_mourner_name" v-model="form.chief_mourner_name" type="text" class="mt-1 block w-full" />
            </div>

            <div class="col-span-6 sm:col-span-2">
                <InputLabel for="relationship" :value="t('registers.relationship_to_deceased')" />
                <TextInput id="relationship" v-model="form.relationship_to_deceased" type="text" class="mt-1 block w-full" placeholder="例：長男、妻" />
            </div>
            <div class="col-span-6  sm:col-span-2">&nbsp;</div>
            <!-- 氏名 -->
            <div class="col-span-6  sm:col-span-3">
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
            </div>             
            <div class="col-span-6 sm:col-span-1">
                <div class="space-y-2">
                    <div class="border-2 border-gray-400 rounded-lg bg-white">
                        <Vue3SignaturePad
                        ref="canvasPad"
                        :options="{ minWidth: 1, maxWidth: 3, penColor: 'black' }"
                        style="width: 100%; height: 128px;"
                        />
                    </div>

                    <div class="flex justify-end">
                        <SecondaryButton type="button" @click="clearPad">
                        {{ t('applications.clear') }}
                        </SecondaryButton>
                    </div>
                </div>
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

            <!-- 年齢 -->
            <div class="col-span-6 sm:col-span-1">
                <InputLabel for="age_at_death" :value="t('registers.age_at_death')" />
                <div class="mt-1 flex items-center gap-2">
                    <TextInput
                        id="age_at_death"
                        v-model="form.age_at_death"
                        type="number"
                        class="w-full"
                    />
                    <span class="text-sm text-gray-500">歳</span>
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
            <!-- 配偶者 -->
            <div class="col-span-6  sm:col-span-2">
                <InputLabel :value="t('registers.spouse_status')" />
                <div class="mt-2 flex flex-wrap items-center gap-6">
                    <label class="flex items-center gap-2">
                        <input type="radio" v-model="form.spouse_status" value="alive"
                              class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">有</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="radio" v-model="form.spouse_status" value="none"
                              class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">無</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="radio" v-model="form.spouse_status" value="deceased"
                              class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm">死別</span>
                    </label>

                </div>
            </div>

            <!-- 子・孫 -->
            <div class="col-span-6  sm:col-span-2">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <InputLabel value="子" />
                        <div class="mt-1 flex items-center gap-2">
                            <TextInput v-model="form.children_count" type="number" class="w-full" />
                            <span class="text-sm text-gray-500">人</span>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="孫" />
                        <div class="mt-1 flex items-center gap-2">
                            <TextInput v-model="form.grandchildren_count" type="number" class="w-full" />
                            <span class="text-sm text-gray-500">人</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 背景色 -->
            <div class="col-span-6 sm:col-span-3">
                <p class="">{{ t('registers.bg_color') }}</p>
                <div class="flex flex-wrap gap-4">

                    <div v-for="color in colors"
                        :key="'bg-'+color"
                        @click="form.bg_color = color"
                        class="cursor-pointer">
                        <img :src="getImageUrl(color)" class="!w-16 !h-16 !max-w-none rounded border-4"
                            :class="form.bg_color === color
                            ? 'ring-4 ring-black scale-105'
                            : 'hover:scale-105'"
                        />
                    </div>

                </div>
            </div>    
            <div class="col-span-6 sm:col-span-3">
                <p class="">{{ t('registers.text_color') }}</p>
                <div class="flex flex-wrap gap-4">
                    <div v-for="color in text_colors"
                        :key="'text_'+color"
                        @click="form.text_color = color"
                        class="cursor-pointer">
                        <img :src="getTextImageUrl(color)" class="!w-16 !h-16 !max-w-none rounded border-4"
                            :class="form.text_color === color
                            ? 'ring-4 ring-black scale-105'
                            : 'hover:scale-105'"
                        />
                    </div>

                </div>
            </div> 


            <!-- 性格（チェックボックス） -->
            <div class="col-span-6">
                <InputLabel :value="t('registers.traits')" />
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-y-2">
                    <div v-for="trait in traitsOptions" :key="trait" class="flex items-center">
                        <Checkbox :id="trait" v-model:checked="form.traits" :value="trait" />
                        <label :for="trait" class="ml-2 text-sm text-gray-600">{{ trait }}</label>
                    </div>
                </div>
            </div>

            <!-- 自由記述 -->
            <div class="col-span-6">
                <InputLabel for="special_notes" :value="t('registers.special_notes')" />
                <textarea id="special_notes" v-model="form.special_notes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
            </div>

            <div class="col-span-6">
                <InputLabel for="remarks" :value="t('registers.note')" />
                <textarea id="remarks" v-model="form.remarks" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2"></textarea>
            </div>
        </template>

        <template #actions>       
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ t('registers.send') }}
            </PrimaryButton>
        </template>
    </FormSection>
  </AppLayout>    
</template>
