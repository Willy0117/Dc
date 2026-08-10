<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

// Heroicons
import {
  Home, Users, User, ShieldCheck, Award, ClipboardList,
  Building2, Menu, KeyRound, UserCog,
  X, Key, GraduationCap, Receipt, Video, Eye,
  FileCheck, Download,
  RefreshCw,
  FileText, BadgeDollarSign, 
  CreditCard,
  Calendar,
  CheckCircle2,
  ArrowLeft, Settings, HardDrive
} from 'lucide-vue-next'

const page = usePage()

const mobileOpen = ref(false)       // モバイル用の開閉状態

const collapsed = ref(false)
const openSubMenu = ref(null)

const toggleCollapse = () => (collapsed.value = !collapsed.value)
const toggleSubMenu = (menu) => (openSubMenu.value = openSubMenu.value === menu ? null : menu)

const { props } = usePage()
console.log(props)
// Jetstream props
const authUser = props.auth.user
const currentTeam = authUser.current_team
const currentTeamId = authUser.current_team_id
const allTeams = authUser.all_teams
const hasApiFeatures = props.jetstream.hasApiFeatures
const hasTeamFeatures = props.jetstream.hasTeamFeatures
const canCreateTeams = props.jetstream.canCreateTeams

const { t, locale } = useI18n()

// レスポンシブ判定
/*const isMobile = ref(false)
const handleResize = () => { isMobile.value = window.innerWidth < 1024 }

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})
onBeforeUnmount(() => window.removeEventListener('resize', handleResize))
*/
// ページ遷移でサブメニュー閉じる
//watch(() => router.page, () => { openSubMenu.value = null })

// collapsed 状態保存
//watch(collapsed, val => { localStorage.setItem('sidebar-collapsed', JSON.stringify(val)) })

// ページURLに応じて初期サブメニューを決定
onMounted(() => {
  if (page.url.startsWith('/menus') || page.url.startsWith('/menus/weekly') || page.url.startsWith('/menus/import')) {
    openSubMenu.value = 'menus'
  }
  if (page.url.startsWith('/tenants') || page.url.startsWith('/roles') || page.url.startsWith('/permissions')) {
    openSubMenu.value = 'access'
  }
  if (page.url.startsWith('/devices') || page.url.startsWith('/operators') || page.url.startsWith('/sensors') || page.url.startsWith('/processes') ) {
    openSubMenu.value = 'masters'
  }
  if (page.url.startsWith('/users')) {
    openSubMenu.value = 'users'
  }
  if (page.url.startsWith('/reports')) {
    openSubMenu.value = 'reports'
  }
  if (page.url.startsWith('/exams')) {
    openSubMenu.value = 'exams'
  }
})

// ヘッダー操作
const logout = () => { router.post(route('logout')) }
const switchTeam = (team) => { router.put(route('current-team.update'), { team_id: team.id }) }
const isActive = (name) => route().current(name)

// ---------------------------
// Permission helper (Vue側)
// ---------------------------
const user = usePage().props.auth.user || null

console.log(user)

const can = (permissionName) => {
  if (!user) return false

  // permissions を安全に配列化
  const perms = Array.isArray(user.permissions)
    ? user.permissions
    : (user.permissions?.data ?? [])
  if (perms.length > 0) {
    return perms.some(p => p.name === permissionName)
  }

  // role を配列化
  const roles = Array.isArray(user.roles) ? user.roles : (user.roles?.data ?? [])

  if (roles.length > 0) {
    // Super Admin は全権限
    if (roles.some(r => ['super admin', 'super-admin'].includes(r.name.toLowerCase()))) {
      return true
    }

    // Tenant Admin は一部権限のみ
    if (roles.some(r => r.name.toLowerCase().startsWith('tenant_admin'))) {
      return ['manage roles', 'manage permissions'].includes(permissionName)
    }
  }

  return false
}

// showAccessControl : セクション丸ごと表示判定
const showAccessControl = computed(() => {
  if (!user) return false

  // Super Admin は全て表示
  if (user.roles?.some(r => r.name.toLowerCase() === 'super admin')) {
    return true
  }

  // テナント管理者は role / permission のみ表示
  return can('manage roles') || can('manage permissions')
})
</script>

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

       <nav class="flex-1 overflow-y-auto px-2 py-4 text-sm">
      <!-- Dashboard -->
      <Link :href="route('dashboard')"
            class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
            :class="isActive('dashboard') ? 'bg-gray-300 font-semibold' : ''">
        <Home class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">{{ t('dashboard') }}</span>
      </Link>

      <Link
          :href="route('elearning.history')"
          class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          :class="isActive('elearning.history') ? 'bg-gray-300 font-semibold' : ''">
          <ClipboardList class="w-5 h-5 mr-1"/>
          受験結果一覧
      </Link>
      
      <Link
          :href="route('elearning.index')"
          class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          :class="isActive('elearning.index') ? 'bg-gray-300 font-semibold' : ''">
          <ClipboardList class="w-5 h-5 mr-1"/>
          e-ラーニング確認テスト
      </Link>
      
      <Link :href="route('reference-videos.index')"
            class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
            :class="isActive('reference-videos.index') ? 'bg-gray-300 font-semibold' : ''">
        <Video class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">動注ライセンス契約動画一覧</span>
      </Link>

      <Link :href="route('resource-documents.index')"
            class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
            :class="isActive('resource-documents.index') ? 'bg-gray-300 font-semibold' : ''">
        <FileText class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">動注ライセンス契約資料一覧</span>
      </Link>

      <div class="mt-2">
        <button
          @click="toggleSubMenu('reports')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <ClipboardList class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">動注ライセンス症例報告</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'reports' }"
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
          <div v-show="openSubMenu === 'reports' && !collapsed" class="pl-6 mt-1 space-y-1">     
            <Link :href="route('reports.index')"
                  class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
                  :class="isActive('reports.index') ? 'bg-gray-300 font-semibold' : ''">
              <ClipboardList class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">動注ライセンス症例報告一覧</span>
            </Link>
          
            <Link :href="route('reports.create')"
                  class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
                  :class="isActive('reports.create') ? 'bg-gray-300 font-semibold' : ''">
              <ClipboardList class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">動注ライセンス症例報告</span>
            </Link>
            <Link
                :href="route('procedure-videos.index')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
                :class="isActive('procedure-videos.index') ? 'bg-gray-300 font-semibold' : ''">
                <Video class="w-5 h-54 mr-1"/>
                手技動画アップロード
            </Link>        
         </div>   
        </transition>
      </div>

      <Link :href="route('licenses.index')"
            class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
            :class="isActive('licenses.index') ? 'bg-gray-300 font-semibold' : ''">
        <Download class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">ライセンス証ダウンロード</span>
      </Link>

      <Link
          :href="route('profile.edit')"
          class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
          :class="isActive('profile.edit') ? 'bg-gray-300 font-semibold' : ''">
          <UserCog class="w-5 h-5 mr-1"/>
          プロフィール編集
      </Link>

<!--      
      <Link :href="route('pdf-uploads.create')"
            class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
            :class="isActive('pdf-uploads.create') ? 'bg-gray-300 font-semibold' : ''">
        <DocumentIcon class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">{{ t('instructors.update') }}</span>
      </Link>

      <Link :href="route('pdf-uploads.index')"
            class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
            :class="isActive('pdf-uploads') ? 'bg-gray-300 font-semibold' : ''">
        <DocumentIcon class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">{{ t('credit_acquisition') }}</span>
      </Link>
      <Link :href="route('annual-fees.index')"
            class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
            :class="isActive('annual-fees') ? 'bg-gray-300 font-semibold' : ''">
        <DocumentCurrencyYenIcon class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">{{ t('annual_fees.annual_fee') }}</span>
      </Link -->
    </nav>
  </aside>
      <!-- モバイルオーバーレイ -->
  <div
    v-if="mobileOpen"
    class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    @click="mobileOpen = false"
  ></div>
  </div>
  <style>
    .slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.2s ease; }
    .slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; max-height: 0; }
    .slide-fade-enter-to, .slide-fade-leave-from { opacity: 1; max-height: 500px; }
  </style>
</template>



