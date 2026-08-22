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
        <!-- ここに追加 -->
        <Link
          v-if="can('notice.view') || can('notice.edit')"
          :href="route('admin.notices.index')"
          class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
          :class="isActive('admin.notices.index') ? 'bg-gray-200 font-semibold' : ''"
        >
          <Megaphone class="w-4 h-4 mr-1"/>
          お知らせ管理
        </Link>

        <!-- Members サブメニュー -->
        <div v-if="canAccessMenu('members')" class="mt-2">
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
        <div v-if="canAccessMenu('organizations')" class="mt-2">
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
                <Link
                  :href="route('admin.storage.index')"
                  class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                  :class="isActive('admin.storage.index') ? 'bg-gray-200 font-semibold' : ''"
                >
                  <HardDrive class="w-4 h-4 mr-1"/>
                  ファイル一覧
                </Link>
            </div>
          </transition>
        </div>

        <!-- 請求 サブメニュー -->
        <div v-if="canAccessMenu('billing')" class="mt-2">
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
                v-if="can('invoice.view') || can('invoice.edit')"
                :href="route('admin.invoices.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.invoices.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <FileText class="w-4 h-4 mr-1"/>
                請求書一覧
              </Link>
              <Link
                v-if="can('stripe.view') || can('stripe.edit')"
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
        <Link
          :href="route('admin.profile-change-logs.index')"
          class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
          :class="isActive('admin.profile-change-logs.index') ? 'bg-gray-200 font-semibold' : ''"
        >
          <History class="w-4 h-4 mr-1"/>
          プロフィール変更履歴
        </Link>
        <!-- 症例報告 サブメニュー -->
        <div v-if="canAccessMenu('case_reports')" class="mt-2">
          <button
            @click="toggleSubMenu('case_reports')"
            class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          >
            <div class="flex items-center">
              <ClipboardList class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">症例報告</span>
            </div>
            <svg
              v-if="!collapsed"
              :class="{ 'rotate-90': openSubMenu === 'case_reports' }"
              class="w-4 h-4 transform transition-transform duration-200"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu === 'case_reports' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link
                :href="route('admin.case-reports.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.case-reports.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <ClipboardList class="w-4 h-4 mr-1"/>
                症例報告一覧
              </Link>
              <Link
                :href="route('admin.procedure-videos.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.procedure-videos.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <Video class="w-4 h-4 mr-1"/>
                手技動画症例一覧
              </Link>


              <Link
                :href="route('admin.form-fields.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.form-fields.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <Settings class="w-4 h-4 mr-1"/>
                フォーム項目管理
              </Link>
            </div>
          </transition>
        </div>
        <!-- Reference Video サブメニュー -->
        <div v-if="canAccessMenu('references')" class="mt-2">
          <button
            @click="toggleSubMenu('references')"
            class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          >
            <div class="flex items-center">
              <Video class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">動注ライセンス契約動画</span>
            </div>
            <svg
              v-if="!collapsed"
              :class="{ 'rotate-90': openSubMenu === 'references' }"
              class="w-4 h-4 transform transition-transform duration-200"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu === 'references' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link
                :href="route('admin.reference-videos.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.reference-videos.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <Video class="w-4 h-4 mr-1"/>
                契約動画一覧
              </Link>

              <Link
                :href="route('admin.reference-videos.views')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.reference-videos.views') ? 'bg-gray-200 font-semibold' : ''"
              >
                <Eye class="w-4 h-4 mr-1"/>
                視聴状況一覧
              </Link>
            </div>
          </transition>
        </div>
        <!-- Reference Document サブメニュー -->
        <div v-if="canAccessMenu('resources')" class="mt-2">
          <button
            @click="toggleSubMenu('resources')"
            class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          >
            <div class="flex items-center">
              <FileText class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">動注ライセンス契約資料</span>
            </div>
            <svg
              v-if="!collapsed"
              :class="{ 'rotate-90': openSubMenu === 'resources' }"
              class="w-4 h-4 transform transition-transform duration-200"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu === 'resources' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link
                :href="route('admin.resource-documents.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.resource-documents.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <FileText class="w-4 h-4 mr-1"/>
                契約資料一覧
              </Link>
           </div>
          </transition>
        </div>
        <!-- e-ラーニング サブメニュー -->
        <div v-if="canAccessMenu('elearnings')" class="mt-2">
          <button
            @click="toggleSubMenu('elearnings')"
            class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          >
            <div class="flex items-center">
              <ClipboardList class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">e-ラーニング</span>
            </div>
            <svg
              v-if="!collapsed"
              :class="{ 'rotate-90': openSubMenu === 'elearnings' }"
              class="w-4 h-4 transform transition-transform duration-200"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu === 'elearnings' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link
                :href="route('admin.elearning-questions.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.elearning-questions.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <FileText class="w-4 h-4 mr-1"/>
                問題一覧
              </Link>
              <Link
                :href="route('admin.elearning-attempts.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                :class="isActive('admin.elearning-attempts.index') ? 'bg-gray-200 font-semibold' : ''"
              >
                <ClipboardList class="w-4 h-4 mr-1"/>
                受験結果一覧
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

        <!-- Users サブメニュー -->
        <div v-if="canAccessMenu('users')" class="mt-2">
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
  Home, Users, User, ShieldCheck, Award, ClipboardList, History,
  Building2, Menu, KeyRound, UserCog, Eye,
  X, Key, GraduationCap, Receipt, Video,
  FileCheck,
  RefreshCw,Megaphone,
  FileText, BadgeDollarSign,
  CreditCard,
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
  members:       ['member.view',       'member.edit'],
  organizations: ['organization.view', 'organization.edit'],
  billing:       ['invoice.view',      'invoice.edit', 'stripe.view', 'stripe.edit'],
  license_fees:  ['license_fee.view',  'license_fee.edit'],
  access:        ['tenant.view',       'tenant.edit', 'role.view', 'role.edit', 'permission.view', 'permission.edit'],
  admins:        ['admin.view',        'admin.edit'],
  users:         ['user.view',         'user.edit'],
  case_reports:  ['case_report.view',  'case_report.edit'],
  references:    ['reference.view',    'reference.edit'],  
  resources:     ['resource.view',     'resource.edit'],
  elearnings:    ['elearning.view',    'elearning.edit'],  
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
  invoices:      'billing',
  stripe:        'billing',
  license_fees:  'license_fees',
  organizations: 'organizations',
  admins:        'admins',
  case_reports:  'case_reports',
  form_fields:   'case_reports',
  procedure_videos: 'case_reports',
  references:    'references',
  resources:     'resources',
  elearnings:    'elearnings',
}

const validMenus = ['members', 'organizations', 'users', 'access', 'billing', 'license_fees', 'admins', 'case_reports', 'references','resources','elearnings']

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