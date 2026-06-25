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

      <!-- Members サブメニュー -->
      <div class="mt-2">
        <button
          @click="toggleSubMenu('members')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <Users class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('member') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'members' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <transition name="slide-fade">
          <div v-show="openSubMenu === 'members' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link
              :href="route('admin.members.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              :class="isActive('admin.members.index') ? 'bg-gray-200 font-semibold' : ''"
            >
              <Users class="w-4 h-4 mr-1"/>
              {{ t('member') }}
            </Link>
          </div>
        </transition>
      </div>

      <!-- Organizations サブメニュー -->
      <div class="mt-2">
        <button
          @click="toggleSubMenu('organizations')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <Building2 class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('契約先') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'organizations' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <transition name="slide-fade">
          <div v-show="openSubMenu === 'organizations' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link
              :href="route('admin.organizations.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              :class="isActive('admin.organizations.index') ? 'bg-gray-200 font-semibold' : ''"
            >
              <Building2 class="w-4 h-4 mr-1"/>
              {{ t('契約先') }}
            </Link>
          </div>
        </transition>
      </div>

      <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
      <!-- 請求 サブメニュー（新規追加）           -->
      <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
      <div class="mt-2">
        <button
          @click="toggleSubMenu('billing')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <FileText class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">請求</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'billing' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <transition name="slide-fade">
          <div v-show="openSubMenu === 'billing' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link
              :href="route('admin.invoices.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              :class="isActive('admin.invoices.index') ? 'bg-gray-200 font-semibold' : ''"
            >
              <FileText class="w-4 h-4 mr-1"/>
              請求書一覧
            </Link>
            <Link
              :href="route('admin.stripe.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              :class="isActive('admin.stripe.index') ? 'bg-gray-200 font-semibold' : ''"
            >
              <CreditCard class="w-4 h-4 mr-1"/>
              Stripe支払い
            </Link>
          </div>
        </transition>
      </div>
      <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->

<!-- license_fees サブメニュー -->
      <div v-if="showAccessControl" class="mt-2">
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
            <Link :href="route('admin.license-fees.index')"
                  class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
                  :class="isActive('admin.license-fees.index') ? 'bg-gray-300 font-semibold' : ''">
              <BadgeDollarSign class="w-4 h-4 mr-1" />
              <span v-if="!collapsed" class="ml-2">ライセンス料</span>
            </Link>
          </div>
        </transition>
      </div>

      <!-- Access Control -->
      <div v-if="showAccessControl" class="mt-2">
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
              v-if="can('manage tenants')"
              :href="route('admin.tenants.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
            >
              <Building2 class="w-4 h-4 mr-1"/>
              {{ t('navigations.tenants') }}
            </Link>
            <Link
              v-if="can('manage roles')"
              :href="route('admin.roles.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
            >
              <UserCog class="w-4 h-4 mr-1"/>
              {{ t('navigations.roles') }}
            </Link>
            <Link
              v-if="can('manage permissions')"
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
      <div class="mt-2">
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

      <!-- Users サブメニュー -->
      <div class="mt-2">
        <button
          @click="toggleSubMenu('users')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <Users class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('user') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'users' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <transition name="slide-fade">
          <div v-show="openSubMenu === 'users' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link
              :href="route('admin.users.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              :class="isActive('admin.users.index') ? 'bg-gray-200 font-semibold' : ''"
            >
              <Users class="w-4 h-4 mr-1"/>
              {{ t('user') }}
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
  FileText, BadgeDollarSign,
  CreditCard, // ← Stripeアイコン（新規追加）
  Calendar,
  CheckCircle2,
  ArrowLeft, Settings
} from 'lucide-vue-next'

const page = usePage()

const mobileOpen = ref(false)
const collapsed  = ref(false)
const openSubMenu = ref(null)

const toggleCollapse = () => (collapsed.value = !collapsed.value)

const { props } = usePage()
const user = props.auth.admin ?? props.auth.user

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const isActive = (routeName) => false
const hasApiFeatures = true
const showAccessControl = true
const can = (permission) => true

// ──────────────────────────────────────────
// グループマッピング（billing を追加）
// ──────────────────────────────────────────
const groupMap = {
  tenants:     'access',
  roles:       'access',
  permissions: 'access',
  invoices:    'billing', // ← 追加
  stripe:      'billing', // ← 追加
  license_fees: 'license_fees',
}

// 有効なメニュー（billing を追加）
const validMenus = ['member', 'users', 'access', 'billing', 'license_fees']

const detectMenu = () => {
  const current = route().current()
  if (!current) return null

  const parts = current.split('.')
  if (parts.length < 2) return null

  let key = parts[1]

  if (groupMap[key]) {
    key = groupMap[key]
  }

  if (validMenus.includes(key)) {
    return key
  }

  return null
}

onMounted(() => {
  const saved = localStorage.getItem('openMenu')
  if (saved) {
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