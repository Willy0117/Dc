<template>
  <div class="flex">
    <!-- モバイル用ハンバーガー -->
    <button
      @click="mobileOpen = !mobileOpen"
      class="lg:hidden p-2 rounded-full hover:bg-gray-200"
    >
      <template v-if="mobileOpen">
        <XMarkIcon class="w-5 h-5 text-gray-600" />
      </template>
      <template v-else>
        <Bars3Icon class="w-5 h-5 text-gray-600" />
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
        <button
          @click="toggleCollapse"
          class="p-2 rounded-full hover:bg-gray-200"
        >
          <template v-if="collapsed">
            <Bars3Icon class="w-5 h-5 text-gray-600" />
          </template>
          <template v-else>
            <XMarkIcon class="w-5 h-5 text-gray-600" />
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
        <HomeIcon class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">{{ t('dashboard') }}</span>
      </Link>
      <div class="mt-2">
        <button
          @click="toggleSubMenu('members')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <UsersIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('member') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'members' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
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
              <UserIcon class="w-4 h-4 mr-1"/>
              {{ t('member') }}
            </Link>
          </div>
        </transition>
      </div> 

      <!-- 指導士資格認定 -->
      <div class="mt-2">
        <button
          @click="toggleSubMenu('member')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <UsersIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('指導士資格認定') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'member' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <transition name="slide-fade">
          <div v-show="openSubMenu === 'member' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link
              :href="route('admin.exams.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
              :class="isActive('exams.index') ? 'bg-gray-300 font-semibold' : ''"
            >
              <UsersIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('exams.application') }}</span>
            </Link>
            <!-- Link
              :href="route('admin.instructorMembers.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
              :class="isActive('instructorMembers.index') ? 'bg-gray-300 font-semibold' : ''"
            >
              <UsersIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('instructors.member') }}</span>
            </Link -->

          </div>
        </transition>
      </div>   
      <!-- 指導士認定更新 -->
      <div class="mt-2">
        <button
          @click="toggleSubMenu('member')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <UsersIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('指導士認定更新') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'member' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <transition name="slide-fade">
          <div v-show="openSubMenu === 'member' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link
              :href="route('admin.instructorMembers.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
              :class="isActive('instructorMembers.index') ? 'bg-gray-300 font-semibold' : ''"
            >
              <UsersIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('指導士認定更新申請') }}</span>
            </Link>
                       <!-- Members -->
            <Link
              :href="route('admin.pdf-uploads.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
              :class="isActive('admin.pdf-uploads.index') ? 'bg-gray-300 font-semibold' : ''"
            >
              <UsersIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('単位取得申請') }}</span>
            </Link>

          </div>
        </transition>
      </div>          

      <!-- Membes サブメニュー -->
     
      <!-- Organizations サブメニュー -->
      <div class="mt-2">
        <button
          @click="toggleSubMenu('organizations')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <UsersIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('organization') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'organizations' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
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
              <UserIcon class="w-4 h-4 mr-1"/>
              {{ t('organization') }}
            </Link>
          </div>
        </transition>
      </div>
      <div v-if="showAccessControl" class="mt-2">
        <button
          @click="toggleSubMenu('annual_fees')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <DocumentCurrencyYenIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('annual_fees.annual_fee') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'annual_fees' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <transition name="slide-fade">
          <div v-show="openSubMenu === 'annual_fees' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link :href="route('admin.annual-fees.index')"
                  class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
                  :class="isActive('admin.annual-fees') ? 'bg-gray-300 font-semibold' : ''">
              <DocumentCurrencyYenIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('annual_fees.annual_fee') }}</span>
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
            <ShieldCheckIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('access_control') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'access' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
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
              <BuildingOfficeIcon class="w-4 h-4 mr-1"/>
              {{ t('navigations.tenants') }}
            </Link>
            <Link
              v-if="can('manage roles')"
              :href="route('admin.roles.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
            >
              <UsersIcon class="w-4 h-4 mr-1"/>
              {{ t('navigations.roles') }}
            </Link>
            <Link
              v-if="can('manage permissions')"
              :href="route('admin.permissions.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
            >
              <TicketIcon class="w-4 h-4 mr-1"/>
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
            <UsersIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('admin') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'admins' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
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
              <UserIcon class="w-4 h-4 mr-1"/>
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
            <UsersIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('user') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'users' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
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
              <UserIcon class="w-4 h-4 mr-1"/>
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
  HomeIcon, DocumentCurrencyYenIcon,
  UsersIcon, UserIcon,
  ShieldCheckIcon,
  BuildingOfficeIcon,
  ServerIcon,Bars3Icon, XMarkIcon, ArrowUturnLeftIcon,
  TicketIcon,UserPlusIcon, UserMinusIcon, ArrowRightIcon
} from '@heroicons/vue/24/outline'

const page = usePage()

const mobileOpen = ref(false)       // モバイル用の開閉状態

const collapsed = ref(false)
const openSubMenu = ref(null)

const toggleCollapse = () => (collapsed.value = !collapsed.value)
//const toggleSubMenu = (menu) => (openSubMenu.value = openSubMenu.value === menu ? null : menu)

const { props } = usePage()

const user = props.auth.admin ?? props.auth.user

// i18n
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const isActive = (routeName) => false
const hasApiFeatures = true
const showAccessControl = true
const can = (permission) => true

// 例外マッピング
const groupMap = {
  tenants: 'access',
  roles: 'access',
  permissions: 'access',
}

// 有効なメニュー（安全対策）
const validMenus = ['member', 'users', 'access']

// 自動判定
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

// 初期化
onMounted(() => {
  // localStorage優先
  const saved = localStorage.getItem('openMenu')

  if (saved) {
    openSubMenu.value = saved
  } else {
    openSubMenu.value = detectMenu()
  }
})

// 状態保存
watch(openSubMenu, (val) => {
  if (val) {
    localStorage.setItem('openMenu', val)
  }
})

// トグル
const toggleSubMenu = (menu) => {
  openSubMenu.value = openSubMenu.value === menu ? null : menu
}
/*
onMounted(() => {
  if (page.url.startsWith('/admin/member') ) {
    openSubMenu.value = 'member'
  }
  if (page.url.startsWith('/admin/tenants') ||
      page.url.startsWith('/admin/roles') ||
      page.url.startsWith('/admin/permissions')) {
    openSubMenu.value = 'access'
  }
  if (page.url.startsWith('/admin/users') ) {
    openSubMenu.value = 'users'
  }
})
*/

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
