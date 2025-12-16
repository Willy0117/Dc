<template>
  <AppLayout>
    <template #header>{{ t('add_menu') }}</template>

    <div class="p-6">
      <div class="space-y-4">
        <!-- 料理名 -->
        <div>
          <label class="block">{{ t('dish_name') }}</label>
          <textarea
            v-model="form.dish_name"
            class="border rounded px-3 py-2 w-full"
            rows="2"
          ></textarea>
          <div v-if="errors.dish_name" class="text-red-500 text-sm">{{ errors.dish_name }}</div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- 配膳日 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('serving_date') }}</label>
            <div class="relative">
              <input
                v-model="form.serving_date"
                type="date"
                class="border rounded px-3 py-2 w-full pr-10"
              />
              <span class="absolute inset-y-0 right-2 flex items-center text-gray-400 pointer-events-none">
                <CalendarIcon class="w-5 h-5" />
              </span>
            </div>
          </div>

          <!-- 配膳時間 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('serving_time') }}</label>
            <div class="relative">
              <input
                v-model="form.serving_time"
                type="time"
                class="border rounded px-3 py-2 w-full pr-10"
              />
              <span class="absolute inset-y-0 right-2 flex items-center text-gray-400 pointer-events-none">
                <ClockIcon class="w-5 h-5" />
              </span>
            </div>
          </div>

          <!-- 調理日 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('cooking_date') }}</label>
            <div class="relative">
              <input
                v-model="form.cooking_date"
                type="date"
                class="border rounded px-3 py-2 w-full pr-10"
              />
              <span class="absolute inset-y-0 right-2 flex items-center text-gray-400 pointer-events-none">
                <CalendarIcon class="w-5 h-5" />
              </span>
            </div>
          </div>
        </div>
        <!-- 材料 -->
        <div>
          <label class="block">{{ t('materials') }}</label>
          <textarea
            v-model="form.materials"
            class="border rounded px-3 py-2 w-full"
            rows="3"
          ></textarea>
          <div v-if="errors.materials" class="text-red-500 text-sm">{{ errors.materials }}</div>
        </div>

        <!-- Tenant 選択 (Super Admin のみ) -->
        <div v-if="isSuperAdmin" class="mt-4">
          <label class="block mb-1">{{ t('tenant') }}</label>
          <select v-model="form.tenant_id" class="border rounded px-3 py-2 w-full">
            <option :value="null">{{ t('select_tenant') }}</option>
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
              {{ tenant.name }}
            </option>
          </select>
        </div>
        <input type="hidden" v-model="form.redirect_to" />
        <!-- ボタン -->
        <div class="flex space-x-2">
          <button @click="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            {{ t('save') }}
          </button>
          <button
            type="button"
            @click="router.get(form.redirect_to || route('menus.index', filters))"
            class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
          >
            {{ t('cancel') }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { reactive, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

const props = defineProps({
  filters: Object,
  tenants: Array,      // Super Admin のみ
  user: Object,
  menu: Object, // コピー用に渡される場合
  redirect_to: String,
})

const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super admin')
)

const form = reactive({
  dish_name: props.menu?.dish_name ?? '',
  serving_date: props.menu?.serving_date ?? '',
  serving_time: props.menu?.serving_time ?? '',
  cooking_date: props.menu?.cooking_date ?? '',
  materials: props.menu?.materials ?? '',
  tenant_id: props.menu
  ? props.menu.tenant_id
  : (isSuperAdmin.value ? null : props.user?.tenant_id ?? null),
  redirect_to: props.redirect_to,
})

const errors = reactive({
  dish_name: '', serving_date: '', serving_time: '', cooking_date: '', materials: ''
})

// コピー用データをセット
onMounted(() => {
  if (props.menu) {
    form.dish_name = props.menu.dish_name
    form.serving_date = props.menu.serving_date
    form.serving_time = props.menu.serving_time
    form.cooking_date = props.menu.cooking_date
    form.materials = props.menu.materials
  }
})

const submit = () => {
  router.post(route('menus.store'), form, {
    preserveState: true,
    onSuccess: () => {
      // redirect_to があればそこへ、なければ menus.index
      if (form.redirect_to) {
        router.get(form.redirect_to)
      }
    },
    onError: (errs) => Object.assign(errors, errs)
  })
}
</script>
