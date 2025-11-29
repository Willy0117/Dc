<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

// Heroicons
import {
  HomeIcon,
  UserIcon,
  ServerIcon,
  UsersIcon,
  PlusIcon,
  BuildingOfficeIcon,
  TicketIcon,
  ArrowRightOnRectangleIcon,
  Cog6ToothIcon,
  CubeIcon,
  BeakerIcon,
  ShieldCheckIcon, // ← 追加
} from '@heroicons/vue/24/outline'

const { props } = usePage()

// Jetstream props
const authUser = props.auth.user
const currentTeam = authUser.current_team
const currentTeamId = authUser.current_team_id
const allTeams = authUser.all_teams
const hasApiFeatures = props.jetstream.hasApiFeatures
const hasTeamFeatures = props.jetstream.hasTeamFeatures
const canCreateTeams = props.jetstream.canCreateTeams

// サイドバー状態
const collapsed = ref(JSON.parse(localStorage.getItem('sidebar-collapsed') ?? 'false'))
const openSubMenu = ref(null)

const { t, locale } = useI18n()

// レスポンシブ判定
const isMobile = ref(false)
const handleResize = () => { isMobile.value = window.innerWidth < 1024 }

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})
onBeforeUnmount(() => window.removeEventListener('resize', handleResize))

// トグル
const toggleCollapse = () => { if (!isMobile.value) collapsed.value = !collapsed.value }
const toggleSubMenu = (name) => { openSubMenu.value = openSubMenu.value === name ? null : name }

// ページ遷移でサブメニュー閉じる
watch(() => router.page, () => { openSubMenu.value = null })

// collapsed 状態保存
watch(collapsed, val => { localStorage.setItem('sidebar-collapsed', JSON.stringify(val)) })

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
  <div class="flex min-h-screen bg-gray-100">

    <!-- サイドバー -->
    <aside v-if="!isMobile" :class="['bg-gray-50 text-gray-800 shadow-lg h-full flex flex-col transition-all duration-300 z-50 overflow-hidden', collapsed ? 'w-16' : 'w-64']">
      <div class="flex justify-end p-2">
        <button @click="toggleCollapse" class="text-gray-500 hover:text-gray-700">
          <span v-if="collapsed">➡</span>
          <span v-else>⬅</span>
        </button>
      </div>

      <nav class="flex-1 px-2 py-4">
        <!-- Dashboard -->
        <Link :href="route('dashboard')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
              :class="isActive('dashboard') ? 'bg-gray-300 font-semibold' : ''">
          <HomeIcon class="w-5 h-5"/>
          <span v-if="!collapsed" class="ml-2">{{ t('dashboard') }}</span>
        </Link>

        <div class="mt-2">
          <button @click="toggleSubMenu('rehab')" class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 mt-2">
            <div class="flex items-center">
              <CubeIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('Instructor Application') }}</span>
            </div>
            <svg v-if="!collapsed" :class="{'rotate-90': openSubMenu==='rehab'}" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <transition name="slide-fade">
            <div v-show="openSubMenu==='rehab' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link :href="route('rehab.create')" class="flex items-center py-2 px-2 rounded hover:bg-gray-100">
                {{ t('Application Form') }}
              </Link>
              <Link :href="route('rehab.files.edit')" class="flex items-center py-2 px-2 rounded hover:bg-gray-100">
                {{ t('Upload PDF') }}
              </Link>
            </div>
          </transition>
        </div>
        <div class="mt-2">
          <button @click="toggleSubMenu('pdf')" class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 mt-2">
            <div class="flex items-center">
              <DocumentIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('PDF Uploads') }}</span>
            </div>
            <svg v-if="!collapsed" :class="{'rotate-90': openSubMenu==='pdf'}" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <transition name="slide-fade">
            <div v-show="openSubMenu==='pdf' && !collapsed" class="pl-6 mt-1 space-y-1">
              <!-- PDF Uploads一覧ページ -->
              <Link :href="route('pdf_uploads.index')" class="flex items-center py-2 px-2 rounded hover:bg-gray-100">
                {{ t('Upload PDF') }}
              </Link>
            </div>
          </transition>
        </div>                        
        <!-- Profile サブメニュー -->
        <div class="mt-2">
          <button @click="toggleSubMenu('profile')"
                  class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors">
            <div class="flex items-center">
              <UserIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('profile') }}</span>
            </div>
            <svg v-if="!collapsed" :class="{'rotate-90': openSubMenu==='profile'}" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu==='profile' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link :href="route('profile.show')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('profile.show') ? 'bg-gray-200 font-semibold' : ''">
                <UserIcon class="w-4 h-4 mr-1"/>
                {{ t('profile_setting') }}
              </Link>
              <!-- 会員情報編集メニュー -->
              <Link :href="route('profile.member.edit')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('profile.member.edit') ? 'bg-gray-200 font-semibold' : ''">
                <UserIcon class="w-4 h-4 mr-1"/>
                {{ t('edit_member_info') }}
              </Link>              
              <!-- 会員情報編集メニュー -->
              <Link :href="route('profile.organization.edit')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('profile.organization.edit') ? 'bg-gray-200 font-semibold' : ''">
                <UserIcon class="w-4 h-4 mr-1"/>
                {{ t('edit_organization_info') }}
              </Link>

              <Link v-if="hasApiFeatures" :href="route('api-tokens.index')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('api-tokens.index') ? 'bg-gray-200 font-semibold' : ''">
                <ServerIcon class="w-4 h-4 mr-1"/>
                API Tokens
              </Link>
              <!--button @click="logout" class="flex items-center w-full text-left py-2 px-2 rounded hover:bg-gray-100">
                <ArrowRightOnRectangleIcon class="w-4 h-4 mr-1"/>
                Log Out
              </button -->
            </div>
          </transition>
          <!-- Access Control -->
          <template v-if="showAccessControl">
            <button @click="toggleSubMenu('access')"
                    class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors mt-2">
              <div class="flex items-center">
                <ShieldCheckIcon class="w-5 h-5"/>
                <span v-if="!collapsed" class="ml-2">{{ t('access_control') }}</span>
              </div>
              <svg v-if="!collapsed" :class="{'rotate-90': openSubMenu==='access'}" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </button>

            <transition name="slide-fade">
              <div v-show="openSubMenu==='access' && !collapsed" class="pl-6 mt-1 space-y-1">
                <Link
                  v-if="can('manage tenants')"
                  :href="route('tenants.index')"
                  class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                  :class="isActive('tenants.index') ? 'bg-gray-200 font-semibold' : ''"
                >
                  <BuildingOfficeIcon class="w-4 h-4 mr-1"/>
                  {{ t('tenants') }}
                </Link>

                <Link
                  v-if="can('manage roles')"
                  :href="route('roles.index')"
                  class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                  :class="isActive('roles.index') ? 'bg-gray-200 font-semibold' : ''"
                >
                  <UsersIcon class="w-4 h-4 mr-1"/>
                  {{ t('roles') }}
                </Link>

                <Link
                  v-if="can('manage permissions')"
                  :href="route('permissions.index')"
                  class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                  :class="isActive('permissions.index') ? 'bg-gray-200 font-semibold' : ''"
                >
                  <TicketIcon class="w-4 h-4 mr-1"/>
                  {{ t('permissions') }}
                </Link>
              </div>
            </transition>
          </template>
          <!-- ここからマスター系メニュー -->

          <button @click="toggleSubMenu('masters')"
                  class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors mt-2">
            <div class="flex items-center">
              <Cog6ToothIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('masters') }}</span>
            </div>
            <svg v-if="!collapsed" :class="{'rotate-90': openSubMenu==='masters'}" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          <transition name="slide-fade">
            <div v-show="openSubMenu==='masters' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link :href="route('devices.index')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('devices.index') ? 'bg-gray-200 font-semibold' : ''">
                <CubeIcon class="w-4 h-4 mr-1"/>
                {{ t('devices') }}
              </Link>

              <Link :href="route('operators.index')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('operators.index') ? 'bg-gray-200 font-semibold' : ''">
                <UsersIcon class="w-4 h-4 mr-1"/>
                {{ t('operators') }}
              </Link>

              <Link :href="route('sensors.index')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('sensors.index') ? 'bg-gray-200 font-semibold' : ''">
                <BeakerIcon class="w-4 h-4 mr-1"/>
                {{ t('sensors') }}
              </Link>
              <Link :href="route('processes.index')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('processes.index') ? 'bg-gray-200 font-semibold' : ''">
                <BeakerIcon class="w-4 h-4 mr-1"/>
                {{ t('processes') }}
              </Link>
            </div>
          </transition>          
        </div>
        <!-- ここからユーザーメニュー（マスターとは独立） -->
        <div>
          <button @click="toggleSubMenu('users')"
                  class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors mt-2">
            <div class="flex items-center">
              <UsersIcon class="w-5 h-5"/>
              <span v-if="!collapsed" class="ml-2">{{ t('user') }}</span>
            </div>
            <svg v-if="!collapsed" :class="{'rotate-90': openSubMenu==='users'}" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <transition name="slide-fade">
            <div v-show="openSubMenu==='users' && !collapsed" class="pl-6 mt-1 space-y-1">
              <Link :href="route('users.index')"
                    class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
                    :class="isActive('users.index') ? 'bg-gray-200 font-semibold' : ''">
                <UserIcon class="w-4 h-4 mr-1"/>
                {{ t('user') }}
              </Link>
            </div>
          </transition>
        </div>
        <!-- Teams -->
        <div v-if="hasTeamFeatures" class="mt-4 border-t border-gray-200 pt-2 text-xs text-gray-400 px-2">Manage Team</div>
        <div v-if="hasTeamFeatures" class="mt-1 space-y-1">
          <Link :href="route('teams.show', currentTeam)"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
                :class="isActive('teams.show') ? 'bg-gray-300 font-semibold' : ''">
            <UsersIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">Team Settings</span>
          </Link>
          <Link v-if="canCreateTeams" :href="route('teams.create')"
                class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
                :class="isActive('teams.create') ? 'bg-gray-300 font-semibold' : ''">
            <PlusIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">Create Team</span>
          </Link>
          <div v-for="team in allTeams" :key="team.id">
            <form @submit.prevent="switchTeam(team)">
              <button type="submit" class="flex items-center w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors">
                <svg v-if="team.id===currentTeamId" class="w-4 h-4 mr-2 text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span v-if="!collapsed">{{ team.name }}</span>
              </button>
            </form>
          </div>
        </div>

      </nav>
    </aside>

    <!-- メイン -->
    <div class="flex-1 flex flex-col">
      <header class="bg-white shadow flex items-center justify-between px-4 h-16">
        <h1 class="text-lg font-semibold">My App</h1>

        <div class="flex items-center space-x-2">
          <button v-if="isMobile" class="lg:hidden p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <!-- button @click="logout" class="p-2 text-gray-700 hover:text-gray-900">Logout</button -->
        </div>
      </header>

      <main class="p-4">
        <slot />
      </main>
    </div>
  </div>

  <style>
    .slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.2s ease; }
    .slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; max-height: 0; }
    .slide-fade-enter-to, .slide-fade-leave-from { opacity: 1; max-height: 500px; }
  </style>
</template>



