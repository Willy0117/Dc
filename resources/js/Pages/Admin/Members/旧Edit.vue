<template>
  <AppLayout>
  <div class="member-form">
    <!-- Header -->
    <div class="form-header">
      <div class="header-left">
        <button class="back-btn" @click="$emit('cancel')">
          <ArrowLeft :size="16" />
        </button>
        <div>
          <p class="header-sub">会員管理</p>
          <h1 class="header-title">{{ isEdit ? '会員情報を編集' : '新規会員登録' }}</h1>
        </div>
      </div>
      <div class="header-actions">
        <button class="btn-cancel" @click="$emit('cancel')">キャンセル</button>
        <button class="btn-save" @click="handleSubmit" :disabled="saving">
          <Save :size="16" />
          {{ saving ? '保存中...' : '保存する' }}
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tab-bar">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        class="tab-btn"
        :class="{ active: activeTab === tab.key }"
        @click="activeTab = tab.key"
      >
        <component :is="tab.icon" :size="15" />
        {{ tab.label }}
      </button>
    </div>

    <div class="form-body">

      <!-- ========== TAB 1: 個人情報 ========== -->
      <div v-show="activeTab === 'personal'">
        <div class="section">
          <h2 class="section-title">
            <IdCard :size="16" />基本情報
          </h2>
          <div class="grid-2">
            <div class="field">
              <label>会員番号</label>
              <input v-model="form.member_number" placeholder="例: M-00001" />
            </div>
            <div class="field">
              <label>会員種別</label>
              <select v-model="form.member_type">
                <option value="">選択してください</option>
                <option value="regular">正会員</option>
                <option value="student">学生会員</option>
                <option value="honorary">名誉会員</option>
                <option value="supporting">賛助会員</option>
              </select>
            </div>
            <div class="field">
              <label>会員状況</label>
              <select v-model="form.status_id">
                <option value="">選択してください</option>
                <option :value="1">通常</option>
                <option :value="2">休会</option>
                <option :value="3">退会</option>
              </select>
            </div>
            <div class="field">
              <label>役職</label>
              <input v-model="form.position" placeholder="例: 理事長" />
            </div>
            <div class="field">
              <label>入会日</label>
              <input type="date" v-model="form.joined_at" />
            </div>
            <div class="field">
              <label>退会日</label>
              <input type="date" v-model="form.withdrawn_at" />
            </div>
          </div>
        </div>

        <div class="section">
          <h2 class="section-title">
            <User :size="16" />氏名
          </h2>
          <div class="grid-2">
            <div class="field required">
              <label>姓</label>
              <input v-model="form.last_name" placeholder="山田" />
            </div>
            <div class="field required">
              <label>名</label>
              <input v-model="form.first_name" placeholder="太郎" />
            </div>
            <div class="field">
              <label>姓（かな）</label>
              <input v-model="form.last_name_kana" placeholder="やまだ" />
            </div>
            <div class="field">
              <label>名（かな）</label>
              <input v-model="form.first_name_kana" placeholder="たろう" />
            </div>
          </div>
        </div>

        <div class="section">
          <h2 class="section-title">
            <Calendar :size="16" />基本属性
          </h2>
          <div class="grid-2">
            <div class="field">
              <label>性別</label>
              <select v-model="form.gender">
                <option value="">選択してください</option>
                <option value="male">男性</option>
                <option value="female">女性</option>
                <option value="other">その他</option>
              </select>
            </div>
            <div class="field">
              <label>生年月日</label>
              <input type="date" v-model="form.birthdate" />
            </div>
          </div>
        </div>

        <div class="section">
          <h2 class="section-title">
            <Mail :size="16" />連絡先
          </h2>
          <div class="grid-2">
            <div class="field required">
              <label>メールアドレス（ログインID）</label>
              <input type="email" v-model="form.email" placeholder="example@example.com" />
            </div>
            <div class="field">
              <label>個人メールアドレス</label>
              <input type="email" v-model="form.personal_email" placeholder="personal@example.com" />
            </div>
            <div class="field">
              <label>電話番号</label>
              <input v-model="form.tel" placeholder="03-0000-0000" />
            </div>
            <div class="field">
              <label>携帯番号</label>
              <input v-model="form.mobile" placeholder="090-0000-0000" />
            </div>
            <div class="field">
              <label>FAX</label>
              <input v-model="form.fax" placeholder="03-0000-0000" />
            </div>
          </div>
        </div>
      </div>

      <!-- ========== TAB 2: 所属先・住所 ========== -->
      <div v-show="activeTab === 'affiliation'">
        <div class="section">
          <h2 class="section-title">
            <Building2 :size="16" />所属先
          </h2>
          <div class="grid-1">
            <div class="field">
              <label>所属組織</label>
              <div class="select-with-icon">
                <Search :size="14" class="select-icon" />
                <input
                  v-model="organizationSearch"
                  placeholder="組織名で検索..."
                  @input="searchOrganizations"
                />
              </div>
              <div v-if="organizationResults.length" class="dropdown">
                <button
                  v-for="org in organizationResults"
                  :key="org.id"
                  class="dropdown-item"
                  @click="selectOrganization(org)"
                >
                  <Building2 :size="13" />
                  {{ org.name }}
                  <span v-if="org.abbr" class="abbr">{{ org.abbr }}</span>
                </button>
              </div>
              <div v-if="form.organization_id" class="selected-org">
                <Building2 :size="14" />
                {{ selectedOrganizationName }}
                <button @click="clearOrganization" class="clear-btn">
                  <X :size="12" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="section">
          <div class="section-header-row">
            <h2 class="section-title">
              <MapPin :size="16" />自宅住所
            </h2>
          </div>
          <div class="grid-2">
            <div class="field">
              <label>郵便番号</label>
              <input v-model="homeAddress.postal_code" placeholder="000-0000" @blur="fetchAddress('home')" />
            </div>
            <div class="field">
              <label>都道府県</label>
              <input v-model="homeAddress.address1" placeholder="東京都" />
            </div>
            <div class="field span-2">
              <label>市区町村・番地</label>
              <input v-model="homeAddress.address2" placeholder="千代田区千代田1-1" />
            </div>
            <div class="field span-2">
              <label>ビル名・部屋番号</label>
              <input v-model="homeAddress.address3" placeholder="○○ビル 101号室" />
            </div>
            <div class="field">
              <label>電話番号</label>
              <input v-model="homeAddress.tel" placeholder="03-0000-0000" />
            </div>
          </div>
        </div>

        <div class="section">
          <div class="section-header-row">
            <h2 class="section-title">
              <Send :size="16" />送付先住所
            </h2>
            <label class="checkbox-label">
              <input type="checkbox" v-model="sameAsHome" @change="copyHomeAddress" />
              自宅住所と同じ
            </label>
          </div>
          <div v-if="!sameAsHome" class="grid-2">
            <div class="field">
              <label>郵便番号</label>
              <input v-model="shippingAddress.postal_code" placeholder="000-0000" />
            </div>
            <div class="field">
              <label>都道府県</label>
              <input v-model="shippingAddress.address1" placeholder="東京都" />
            </div>
            <div class="field span-2">
              <label>市区町村・番地</label>
              <input v-model="shippingAddress.address2" placeholder="千代田区千代田1-1" />
            </div>
            <div class="field span-2">
              <label>ビル名・部屋番号</label>
              <input v-model="shippingAddress.address3" placeholder="○○ビル 101号室" />
            </div>
          </div>
          <div v-else class="same-notice">
            <CheckCircle2 :size="14" />自宅住所を送付先として使用します
          </div>
        </div>
      </div>

      <!-- ========== TAB 3: 学歴・学位・学会役職 ========== -->
      <div v-show="activeTab === 'academic'">

        <!-- 学歴 -->
        <div class="section">
          <div class="section-header-row">
            <h2 class="section-title">
              <GraduationCap :size="16" />最終学歴
            </h2>
          </div>
          <div class="grid-2">
            <div class="field span-2">
              <label>学校名</label>
              <input v-model="education.school_name" placeholder="○○大学" />
            </div>
            <div class="field span-2">
              <label>学部・学科名</label>
              <input v-model="education.faculty" placeholder="医学部 医学科" />
            </div>
            <div class="field">
              <label>卒業（予定）年月</label>
              <input v-model="education.graduated_at" placeholder="2000-03" />
            </div>
          </div>
        </div>

        <!-- 取得学位 -->
        <div class="section">
          <div class="section-header-row">
            <h2 class="section-title">
              <Award :size="16" />取得学位
            </h2>
            <button class="btn-add" @click="addDegree" :disabled="degrees.length >= 5">
              <Plus :size="14" />追加
            </button>
          </div>
          <div v-if="degrees.length === 0" class="empty-state">
            <Award :size="24" />
            <p>学位を追加してください</p>
          </div>
          <div v-for="(degree, index) in degrees" :key="index" class="repeat-item">
            <div class="repeat-item-header">
              <span class="repeat-index">学位 {{ index + 1 }}</span>
              <button class="btn-remove" @click="removeDegree(index)">
                <Trash2 :size="13" />
              </button>
            </div>
            <div class="grid-2">
              <div class="field span-2">
                <label>学位名</label>
                <input v-model="degree.degree" placeholder="医学博士" />
              </div>
              <div class="field">
                <label>取得年月</label>
                <input v-model="degree.obtained_at" placeholder="2005-03" />
              </div>
            </div>
          </div>
        </div>

        <!-- 学会役職歴 -->
        <div class="section">
          <div class="section-header-row">
            <h2 class="section-title">
              <Briefcase :size="16" />学会役職歴
            </h2>
            <button class="btn-add" @click="addRole">
              <Plus :size="14" />追加
            </button>
          </div>
          <div v-if="roles.length === 0" class="empty-state">
            <Briefcase :size="24" />
            <p>役職歴を追加してください</p>
          </div>
          <div v-for="(role, index) in roles" :key="index" class="repeat-item">
            <div class="repeat-item-header">
              <span class="repeat-index">役職 {{ index + 1 }}</span>
              <button class="btn-remove" @click="removeRole(index)">
                <Trash2 :size="13" />
              </button>
            </div>
            <div class="grid-2">
              <div class="field span-2">
                <label>担当役職</label>
                <input v-model="role.role" placeholder="理事" />
              </div>
              <div class="field">
                <label>開始年月</label>
                <input v-model="role.started_at" placeholder="2010-04" />
              </div>
              <div class="field">
                <label>終了年月</label>
                <input v-model="role.ended_at" placeholder="2014-03" />
              </div>
            </div>
          </div>
        </div>

        <!-- 学会委員歴 -->
        <div class="section">
          <div class="section-header-row">
            <h2 class="section-title">
              <Users :size="16" />学会委員歴
            </h2>
            <button class="btn-add" @click="addCommittee">
              <Plus :size="14" />追加
            </button>
          </div>
          <div v-if="committees.length === 0" class="empty-state">
            <Users :size="24" />
            <p>委員歴を追加してください</p>
          </div>
          <div v-for="(committee, index) in committees" :key="index" class="repeat-item">
            <div class="repeat-item-header">
              <span class="repeat-index">委員 {{ index + 1 }}</span>
              <button class="btn-remove" @click="removeCommittee(index)">
                <Trash2 :size="13" />
              </button>
            </div>
            <div class="grid-2">
              <div class="field span-2">
                <label>担当委員</label>
                <input v-model="committee.committee" placeholder="編集委員会" />
              </div>
              <div class="field">
                <label>開始年月</label>
                <input v-model="committee.started_at" placeholder="2010-04" />
              </div>
              <div class="field">
                <label>終了年月</label>
                <input v-model="committee.ended_at" placeholder="2014-03" />
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'

import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import {
  ArrowLeft, Save, User, Mail, Calendar, Building2,
  MapPin, Send, GraduationCap, Award, Briefcase, Users,
  Plus, Trash2, Search, X, CheckCircle2, IdCard
} from 'lucide-vue-next'

// Props
const props = defineProps({
  memberId: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits(['saved', 'cancel'])

// 状態
const isEdit = computed(() => !!props.memberId)
const saving = ref(false)
const activeTab = ref('personal')

const tabs = [
  { key: 'personal',    label: '個人情報',        icon: User },
  { key: 'affiliation', label: '所属先・住所',     icon: Building2 },
  { key: 'academic',    label: '学歴・学位・学会役職', icon: GraduationCap },
]

// フォームデータ
const form = ref({
  organization_id:  null,
  member_number:    '',
  position:         '',
  last_name:        '',
  first_name:       '',
  last_name_kana:   '',
  first_name_kana:  '',
  gender:           '',
  birthdate:        '',
  tel:              '',
  mobile:           '',
  fax:              '',
  email:            '',
  personal_email:   '',
  status_id:        '',
  member_type:      '',
  joined_at:        '',
  withdrawn_at:     '',
})

// 住所
const homeAddress = ref({ postal_code: '', address1: '', address2: '', address3: '', tel: '' })
const shippingAddress = ref({ postal_code: '', address1: '', address2: '', address3: '' })
const sameAsHome = ref(false)

// 学歴
const education = ref({ school_name: '', faculty: '', graduated_at: '' })

// 学位
const degrees = ref([])
const addDegree = () => { if (degrees.value.length < 5) degrees.value.push({ degree: '', obtained_at: '' }) }
const removeDegree = (i) => degrees.value.splice(i, 1)

// 役職歴
const roles = ref([])
const addRole = () => roles.value.push({ role: '', started_at: '', ended_at: '' })
const removeRole = (i) => roles.value.splice(i, 1)

// 委員歴
const committees = ref([])
const addCommittee = () => committees.value.push({ committee: '', started_at: '', ended_at: '' })
const removeCommittee = (i) => committees.value.splice(i, 1)

// 組織検索
const organizationSearch = ref('')
const organizationResults = ref([])
const selectedOrganizationName = ref('')

const searchOrganizations = async () => {
  if (organizationSearch.value.length < 1) { organizationResults.value = []; return }
  try {
    const { data } = await axios.get('/api/organizations/search', { params: { q: organizationSearch.value } })
    organizationResults.value = data
  } catch {}
}

const selectOrganization = (org) => {
  form.value.organization_id = org.id
  selectedOrganizationName.value = org.name
  organizationSearch.value = ''
  organizationResults.value = []
}

const clearOrganization = () => {
  form.value.organization_id = null
  selectedOrganizationName.value = ''
}

// 住所コピー
const copyHomeAddress = () => {
  if (sameAsHome.value) {
    shippingAddress.value = { ...homeAddress.value }
  }
}

// 既存データ取得（edit時）
onMounted(async () => {
  if (!isEdit.value) return
  try {
    const { data } = await axios.get(`/api/members/${props.memberId}`)
    Object.assign(form.value, data.member)
    if (data.home_address) Object.assign(homeAddress.value, data.home_address)
    if (data.shipping_address) Object.assign(shippingAddress.value, data.shipping_address)
    if (data.education) Object.assign(education.value, data.education)
    degrees.value    = data.degrees    || []
    roles.value      = data.roles      || []
    committees.value = data.committees || []
    if (data.organization) selectedOrganizationName.value = data.organization.name
  } catch (e) {
    console.error(e)
  }
})

// 保存
const handleSubmit = async () => {
  saving.value = true
  try {
    const payload = {
      member: form.value,
      home_address: homeAddress.value,
      shipping_address: sameAsHome.value ? null : shippingAddress.value,
      education: education.value,
      degrees: degrees.value,
      roles: roles.value,
      committees: committees.value,
    }
    if (isEdit.value) {
      await axios.put(`/api/members/${props.memberId}`, payload)
    } else {
      await axios.post('/api/members', payload)
    }
    emit('saved')
  } catch (e) {
    console.error(e)
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
/* ── Base ── */
.member-form {
  min-height: 100vh;
  background: #f5f5f7;
  font-family: 'Hiragino Sans', 'Hiragino Kaku Gothic ProN', sans-serif;
  color: #1a1a2e;
}

/* ── Header ── */
.form-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 10;
}
.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.back-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
  color: #6b7280;
  transition: all .15s;
}
.back-btn:hover { background: #f3f4f6; color: #111; }
.header-sub  { font-size: 11px; color: #9ca3af; margin: 0 0 2px; }
.header-title { font-size: 18px; font-weight: 700; margin: 0; }
.header-actions { display: flex; gap: 8px; }
.btn-cancel {
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  font-size: 13px;
  cursor: pointer;
  color: #6b7280;
  transition: all .15s;
}
.btn-cancel:hover { background: #f3f4f6; }
.btn-save {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 20px;
  border: none;
  border-radius: 8px;
  background: #1a1a2e;
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all .15s;
}
.btn-save:hover:not(:disabled) { background: #2d2d4e; }
.btn-save:disabled { opacity: .5; cursor: not-allowed; }

/* ── Tabs ── */
.tab-bar {
  display: flex;
  gap: 0;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  padding: 0 24px;
}
.tab-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 12px 20px;
  border: none;
  border-bottom: 2px solid transparent;
  background: none;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  transition: all .15s;
  margin-bottom: -1px;
}
.tab-btn:hover { color: #1a1a2e; }
.tab-btn.active {
  color: #1a1a2e;
  border-bottom-color: #1a1a2e;
  font-weight: 600;
}

/* ── Body ── */
.form-body {
  max-width: 860px;
  margin: 0 auto;
  padding: 24px;
}

/* ── Section ── */
.section {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 20px 24px;
  margin-bottom: 16px;
}
.section-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  margin: 0 0 16px;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.section-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.section-header-row .section-title { margin-bottom: 0; }

/* ── Grid ── */
.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.grid-1 { display: grid; gap: 14px; }
.span-2 { grid-column: span 2; }

/* ── Field ── */
.field { display: flex; flex-direction: column; gap: 5px; }
.field label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
}
.field.required label::after {
  content: ' *';
  color: #ef4444;
}
.field input,
.field select {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  color: #1a1a2e;
  background: #fff;
  transition: border-color .15s;
  outline: none;
}
.field input:focus,
.field select:focus { border-color: #1a1a2e; }

/* ── Organization Search ── */
.select-with-icon { position: relative; }
.select-with-icon input { padding-left: 32px; width: 100%; box-sizing: border-box; }
.select-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  pointer-events: none;
}
.dropdown {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,.08);
  margin-top: 4px;
  overflow: hidden;
}
.dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  width: 100%;
  text-align: left;
  border: none;
  background: none;
  font-size: 13px;
  cursor: pointer;
  color: #374151;
  transition: background .1s;
}
.dropdown-item:hover { background: #f9fafb; }
.abbr { font-size: 11px; color: #9ca3af; margin-left: auto; }
.selected-org {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  padding: 8px 12px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  font-size: 13px;
  color: #166534;
}
.clear-btn {
  margin-left: auto;
  border: none;
  background: none;
  cursor: pointer;
  color: #6b7280;
  display: flex;
  padding: 2px;
}

/* ── Checkbox ── */
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #6b7280;
  cursor: pointer;
}
.same-notice {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 14px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  font-size: 13px;
  color: #1d4ed8;
}

/* ── Repeat items ── */
.repeat-item {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px 16px;
  margin-bottom: 10px;
  background: #fafafa;
}
.repeat-item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.repeat-index {
  font-size: 12px;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.btn-remove {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border: 1px solid #fecaca;
  border-radius: 6px;
  background: #fff;
  color: #ef4444;
  font-size: 12px;
  cursor: pointer;
  transition: all .15s;
}
.btn-remove:hover { background: #fef2f2; }

/* ── Add button ── */
.btn-add {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border: 1px dashed #d1d5db;
  border-radius: 8px;
  background: #fff;
  color: #6b7280;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all .15s;
}
.btn-add:hover:not(:disabled) { border-color: #1a1a2e; color: #1a1a2e; }
.btn-add:disabled { opacity: .4; cursor: not-allowed; }

/* ── Empty state ── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 24px;
  color: #d1d5db;
  font-size: 13px;
}
</style>