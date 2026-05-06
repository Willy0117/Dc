<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

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
import { ref, onMounted,computed } from 'vue';
import Vue3SignaturePad from 'vue3-signature-pad'
import dayjs from 'dayjs';

const { t } = useI18n()

const props = defineProps({
  user: Object,
  member: Object,
  flash: Object, 
})

const form = useForm({
    last_name: props.member?.last_name ?? '',
    first_name: props.member?.first_name ?? '',
    gender:  props.member?.gender ?? '',
});

const canvasPad = ref(null);

const clearPad = () => {
  canvasPad.value?.clearSignature()
}

const submit = () => {
    if (!canvasPad.value.isEmpty()) {
        form.canvas = canvasPad.value.toDataURL(); // PNG base64
    }
    form.post(route('applications.store'), {
        preserveScroll: true,
    });
};

</script>

<template>
  <AppLayout>
  <template #header>{{ t('applications.create') }}</template>
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
                <InputLabel for="funeral" :value="t('registers.funeral_datetime')" />
                <TextInput
                    v-model="form.funeral_datetime"
                    type="datetime-local"
                    :min="minFuneralDatetime"
                    class="mt-1 block w-full"
                    />
            </div>
            <div class="col-span-6 sm:col-span-2">
                <InputLabel for="staff_name" :value="t('registers.staff_name')" />
                <TextInput id="staff_name" v-model="form.staff_name" type="text" class="mt-1 block w-full" />
                <InputError :message="form.errors.staff_name" class="mt-2" />
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
                    <div v-for="color in displayColors"
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
            <div v-if="form.allow_text" class="col-span-6 sm:col-span-3">
                <p class="">{{ t('registers.text_color') }}</p>
                <div class="flex flex-wrap gap-4">
                    <div v-for="color in displayTextColors"
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
