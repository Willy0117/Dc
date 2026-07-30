<template>
  <div class="flex">
    <!-- モバイル用ハンバーガー -->
    <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-full hover:bg-gray-200">
      <template v-if="mobileOpen">
        <X class="w-5 h-5 text-gray-600" />
      </template>
      <template v-else>
        <Menu class="w-5 h-5 text-gray-600" />
      </template>
    </button>

    <!-- サイドバー -->
    <aside
      :class="[
        'bg-gray-100 h-screen flex flex-col transition-all duration-300 z-50',
        collapsed ? 'w-16' : 'w-64',
        mobileOpen ? 'left-0' : '-left-full',
        'fixed top-0 lg:relative lg:left-0 h-screen'
      ]"
    >
      <!-- PC折りたたみボタン -->
      <div class="flex justify-end p-2 flex-none lg:flex">
        <button @click="toggleCollapse" class="p-2 rounded-full hover:bg-gray-200">
          <template v-if="collapsed">
            <Menu class="w-5 h-5 text-gray-600" />
          </template>
          <template v-else>
            <X class="w-5 h-5 text-gray-600" />
          </template>
        </button>
      </div>

      <!-- メニュー -->
      <nav class="flex-1 overflow-y-auto px-2 py-4 text-sm">

        <!-- Dashboard -->
        <Link
          :href="route('admin.dashboard')"
          class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          :class="isActive('dashboard') ? 'bg-gray-300 font-semibold' : ''"
        >
          <Home class="w-5 h-5"/>
          <span v-if="!collapsed" class="ml-2">{{ t('dashboard') }}</span>
        </Link>

        <!-- 動画講習 サブメニュー -->
        <div v-if="canAccessMenu('video_seminar')" class="mt-2">
          <button
            @click="toggleSubMenu('video_seminar')"
            class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          >
            <div class="flex items-center">
              <GraduationCap class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">動画講習</span>
            </div>
            <svg
              v-if="!collapsed"
              :class="{ 'rotate-90': openSubMenu === 'video_seminar' }"
              class="w-4 h-4 transform transition-transform duration-200"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu === 'video_seminar' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link
                v-if="can('video-sets.view') || can('video-sets.update')"
                :href="route('admin.video-sets.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.video-sets.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <GraduationCap class="w-4 h-4 mr-1"/>
                動画セット
              </Link>
              <Link
                v-if="can('orders.view')"
                :href="route('admin.orders.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.orders.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <Receipt class="w-4 h-4 mr-1"/>
                注文一覧
              </Link>
              <Link
                v-if="can('certificates.view')"
                :href="route('admin.certificates.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.certificates.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <Award class="w-4 h-4 mr-1"/>
                発行済み証明書
              </Link>
            </div>
          </transition>
        </div>

        <!-- ライセンス料 サブメニュー -->
        <div v-if="canAccessMenu('license_fees')" class="mt-2">
          <button
            @click="toggleSubMenu('license_fees')"
            class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          >
            <div class="flex items-center">
              <BadgeDollarSign class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">ライセンス料</span>
            </div>
            <svg
              v-if="!collapsed"
              :class="{ 'rotate-90': openSubMenu === 'license_fees' }"
              class="w-4 h-4 transform transition-transform duration-200"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu === 'license_fees' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link
                :href="route('admin.license-fees.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.license-fees.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <BadgeDollarSign class="w-4 h-4 mr-1"/>
                ライセンス料
              </Link>
            </div>
          </transition>
        </div>

        <!-- Access Control サブメニュー -->
        <div v-if="canAccessMenu('access')" class="mt-2">
          <button
            @click="toggleSubMenu('access')"
            class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          >
            <div class="flex items-center">
              <ShieldCheck class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('access_control') }}</span>
            </div>
            <svg
              v-if="!collapsed"
              :class="{ 'rotate-90': openSubMenu === 'access' }"
              class="w-4 h-4 transform transition-transform duration-200"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu === 'access' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link
                v-if="can('tenant.view') || can('tenant.edit')"
                :href="route('admin.tenants.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              >
                <Building2 class="w-4 h-4 mr-1"/>
                {{ t('navigations.tenants') }}
              </Link>
              <Link
                v-if="can('role.view') || can('role.edit')"
                :href="route('admin.roles.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              >
                <UserCog class="w-4 h-4 mr-1"/>
                {{ t('navigations.roles') }}
              </Link>
              <Link
                v-if="can('permission.view') || can('permission.edit')"
                :href="route('admin.permissions.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              >
                <KeyRound class="w-4 h-4 mr-1"/>
                {{ t('navigations.permissions') }}
              </Link>
            </div>
          </transition>
        </div>

        <!-- Admins サブメニュー -->
        <div v-if="canAccessMenu('admins')" class="mt-2">
          <button
            @click="toggleSubMenu('admins')"
            class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          >
            <div class="flex items-center">
              <Users class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('admin') }}</span>
            </div>
            <svg
              v-if="!collapsed"
              :class="{ 'rotate-90': openSubMenu === 'admins' }"
              class="w-4 h-4 transform transition-transform duration-200"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu === 'admins' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link
                :href="route('admin.admins.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.admins.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <Users class="w-4 h-4 mr-1"/>
                {{ t('admin') }}
              </Link>
            </div>
          </transition>
        </div>

      </nav>
    </aside>

    <!-- モバイルオーバーレイ -->
    <div
      v-if="mobileOpen"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
      @click="mobileOpen = false"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  Home, Users, User, ShieldCheck, Award,
  Building2, Menu, KeyRound, UserCog,
  X, Key, GraduationCap, Receipt,
  FileCheck,
  RefreshCw,
  BadgeDollarSign,
  Calendar,
  CheckCircle2,
  ArrowLeft, Settings, HardDrive
} from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

const { props } = usePage()
const { t } = useI18n()

const mobileOpen = ref(false)
const collapsed  = ref(false)
const openSubMenu = ref(null)

const toggleCollapse = () => (collapsed.value = !collapsed.value)

const isActive = (routeName) => false

// ──────────────────────────────────────────
// Permission チェック
// ──────────────────────────────────────────
const can = (permission) => {
  return props.auth?.admin?.permissions?.includes(permission) ?? false
}

// メニューごとに必要なpermissionを定義
const menuPermissions = {
  license_fees:  ['license-fee.view',  'license-fee.edit'],
  access:        ['tenant.view',       'tenant.edit', 'role.view', 'role.edit', 'permission.view', 'permission.edit'],
  admins:        ['admin.view',        'admin.edit'],
  video_seminar: ['video-sets.view', 'video-sets.update', 'video-sets.create', 'orders.view', 'certificates.view'],
}

// いずれか1つでも持っていればtrue
const canAccessMenu = (menu) => {
  return (menuPermissions[menu] ?? []).some(p => can(p))
}

// ──────────────────────────────────────────
// グループマッピング
// ──────────────────────────────────────────
const groupMap = {
  tenants:       'access',
  roles:         'access',
  permissions:   'access',
  admins:        'admins',
  'video-sets':  'video_seminar',
  orders:        'video_seminar',
  certificates:  'video_seminar',
}

const validMenus = ['access', 'license_fees', 'admins', 'video_seminar']

const detectMenu = () => {
  const current = route().current()
  if (!current) return null

  const parts = current.split('.')
  if (parts.length < 2) return null

  let key = parts[1]
  if (groupMap[key]) key = groupMap[key]

  if (validMenus.includes(key) && canAccessMenu(key)) {
    return key
  }

  return null
}

onMounted(() => {
  const saved = localStorage.getItem('openMenu')
  if (saved && canAccessMenu(saved)) {
    openSubMenu.value = saved
  } else {
    openSubMenu.value = detectMenu()
  }
})

watch(openSubMenu, (val) => {
  if (val) {
    localStorage.setItem('openMenu', val)
  }
})

const toggleSubMenu = (menu) => {
  if (!canAccessMenu(menu)) return
  openSubMenu.value = openSubMenu.value === menu ? null : menu
}
</script>

<style>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  max-height: 0;
}
.slide-fade-enter-to,
.slide-fade-leave-from {
  opacity: 1;
  max-height: 500px;
}
</style>