import { reactive, computed } from 'vue'
import type {
  OrganizationFormData,
  OrganizationAddress,
  MemberAddress,
  MemberAddressType,
  Member,
  OrganizationEditProps,
} from '@/types'

export const PREFECTURES = [
  '北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県',
  '茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県',
  '新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県',
  '静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県',
  '奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県',
  '徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県',
  '熊本県','大分県','宮崎県','鹿児島県','沖縄県',
]

export const CONTRACT_STATUS_OPTIONS = [
  { value: 0 as const, label: '新規',              bgClass: 'bg-green-500',   activeClass: 'border-green-500  bg-green-50  text-green-800'  },
  { value: 1 as const, label: '契約中',            bgClass: 'bg-emerald-500', activeClass: 'border-emerald-500 bg-emerald-50 text-emerald-800' },
  { value: 2 as const, label: '契約終了',          bgClass: 'bg-stone-400',   activeClass: 'border-stone-400  bg-stone-50  text-stone-700'  },
  { value: 3 as const, label: '特別枠（請求なし）', bgClass: 'bg-blue-500',    activeClass: 'border-blue-500   bg-blue-50   text-blue-800'   },
  { value: 4 as const, label: 'ドクター個人契約',   bgClass: 'bg-amber-500',   activeClass: 'border-amber-500  bg-amber-50  text-amber-800'  },
  { value: 5 as const, label: '更新しない',         bgClass: 'bg-red-500',     activeClass: 'border-red-500    bg-red-50    text-red-800'    },
] as const

export const PAYMENT_METHOD_OPTIONS = [
  { value: 1 as const, label: '銀行振込' },
  { value: 2 as const, label: 'クレジットカード' },
] as const


export const MEMBER_STATUS_OPTIONS = [
  { value: 1 as const, label: '通常' },
  { value: 2 as const, label: '休会' },
  { value: 3 as const, label: '退会' },
]

export const MIN_MEMBERS = 1

export function makeOrgAddress(): OrganizationAddress {
  return { name: null, postal_code: null, address1: null, address2: null, address3: null, tel: null, fax: null, email: null }
}

export function makeMemberAddress(type: MemberAddressType): MemberAddress {
  return { type, postal_code: null, address1: null, address2: null, address3: null, tel: null, fax: null }
}

export function makeMember(): Member {
  return {
    member_number: null, position: null,
    last_name: '', first_name: '',
    last_name_kana: null, first_name_kana: null,
    gender: null, birthdate: null,
    tel: null, mobile: null, fax: null,
    email: null, personal_email: null,
    status_id: 1, member_type: null,
    joined_at: null, withdrawn_at: null,
    addresses: [makeMemberAddress(1), makeMemberAddress(2)],
  }
}

function toDateString(val: string | null): string | null {
  if (!val) return null
  return val.slice(0, 10)
}

// Controller の edit() が返す props から FormData を組み立てる
function makeFormFromProps(props: OrganizationEditProps): OrganizationFormData {
  return {
    organization: props.organization ? {
      ...props.organization,
      contract_date: toDateString(props.organization.contract_date),
    } : {
      name: '', abbr: null, url: null,
      contract_no: null, contract_date: null, contract_status: 0,
      rep_position: null, rep_last_name: null, rep_first_name: null,
    },
    location_address: props.location_address ?? makeOrgAddress(),
    shipping_address: props.shipping_address ?? makeOrgAddress(),
    billing_address:  props.billing_address  ?? makeOrgAddress(),
    members: props.members?.length
      ? props.members.map(m => ({
          ...m,
          joined_at:    toDateString(m.joined_at),
          withdrawn_at: toDateString(m.withdrawn_at),
          addresses: m.addresses?.length
            ? m.addresses
            : [makeMemberAddress(1), makeMemberAddress(2)],
        }))
      : [makeMember(), makeMember(), makeMember()],
  }
}

export function useOrganizationForm(props: OrganizationEditProps) {
    console.log('useOrganizationForm called')
  console.log('props type:', typeof props)
  console.log('props keys:', Object.keys(props ?? {}))
  const form = reactive<OrganizationFormData>(makeFormFromProps(props))
console.log('form.organization:', form.organization)

  const isValid = computed(() =>
    form.organization.name.trim() !== '' &&
    (form.organization.rep_last_name ?? '').trim() !== '' &&
    (form.organization.rep_first_name ?? '').trim() !== '' &&
    !!form.location_address.email?.trim() &&
    form.members.length >= MIN_MEMBERS &&
    form.members.every(m => m.last_name.trim() !== '' && m.first_name.trim() !== '')
  )

  function addMember() {
    form.members.push(makeMember())
  }

  function removeMember(index: number) {
    if (index >= MIN_MEMBERS) form.members.splice(index, 1)
  }

  // 所在地 → 郵送先 or 請求先 へコピー
  function copyOrgAddress(to: 'shipping_address' | 'billing_address') {
    Object.assign(form[to], { ...form.location_address })
  }

  function copyMemberAddress(memberIndex: number, from: MemberAddressType, to: MemberAddressType) {
    const m = form.members[memberIndex]
    const src = m.addresses.find(a => a.type === from)
    const dst = m.addresses.find(a => a.type === to)
    if (src && dst) Object.assign(dst, { ...src, type: to })
  }

  return { form, isValid, addMember, removeMember, copyOrgAddress, copyMemberAddress }
}
